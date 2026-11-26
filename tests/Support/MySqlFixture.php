<?php

namespace Test\Lucinda\Project\Support;

use Lucinda\Framework\Persistence\SqlExecutor;
use Lucinda\SQL\Connection;
use Lucinda\SQL\DataSource;

/**
 * Owns live MySQL connections and the rows created by one isolated test case
 *
 * The configured database must be the explicitly selected unit_tests database
 * or a framework_dao_test_* schema. Its persistence tables must start empty.
 * An advisory lock serializes fixture owners. Cleanup deletes captured primary
 * keys, without changing the installed schema or resetting identity counters.
 */
final class MySqlFixture
{
    /** Native connection used for fixture arrangement and observation */
    private \PDO $pdo;

    /** Application connection used by the real executor under test */
    private Connection $connection;

    /** Executor injected into production DAO instances */
    private SqlExecutor $executor;

    /** Configuration shared by independent connections to this test schema */
    private DataSource $dataSource;

    /** @var Connection[] Independent connections owned by this fixture */
    private array $additionalConnections = [];

    /** @var SqlExecutor[] Executors whose transactions require cleanup */
    private array $additionalExecutors = [];

    /** Database-specific lock retained until all fixture rows are removed */
    private string $lockName;

    /** Whether this fixture has acquired ownership of the empty test tables */
    private bool $owned = false;

    /**
     * @var array<string, string[]> Tables and primary keys in cleanup order
     */
    private array $keys = [
        'users_resources' => ['user_id', 'resource_id', 'http_method'],
        'roles_resources' => ['role_id', 'resource_id', 'http_method'],
        'users_roles' => ['user_id', 'role_id'],
        'user_credentials' => ['user_id'],
        'oauth2_identities' => ['vendor_name', 'remote_id'],
        'user_mfa' => ['user_id'],
        'oauth2_approvals' => ['vendor_name', 'remote_id'],
        'oauth2_states' => ['state'],
        'authentication_throttles' => ['bucket'],
        'sessions' => ['id'],
        'migrations' => ['class_name'],
        'resources' => ['id'],
        'roles' => ['id'],
        'users' => ['id'],
    ];

    /**
     * Opens the installed MySQL test schema configured in unit-tests.xml
     *
     * @throws \RuntimeException If configuration or isolation is invalid
     * @throws \Throwable If MySQL cannot open the configured test connections
     */
    public function __construct()
    {
        $xml = simplexml_load_file(dirname(__DIR__, 2) . '/unit-tests.xml');
        $environment = getenv('ENVIRONMENT') ?: 'local';
        $server = $xml->servers->sql->{$environment}->server;
        $schema = (string) $server['schema'];
        if (
            (string) $server['driver'] !== 'mysql'
            || ($schema !== 'unit_tests'
                && !preg_match('/^framework_dao_test_[a-z0-9_]+$/D', $schema))
        ) {
            throw new \RuntimeException(
                'A dedicated MySQL test schema is required'
            );
        }
        $dsn = 'mysql:host=' . (string) $server['host']
            . ';port=' . ((string) $server['port'] ?: '3306')
            . ';dbname=' . $schema . ';charset=utf8mb4';
        $this->pdo = new \PDO(
            $dsn,
            (string) $server['username'],
            (string) $server['password'],
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        $this->lockName = 'lucinda-unit-tests:' . $schema;
        $lock = $this->pdo->prepare('SELECT GET_LOCK(?, 5)');
        $lock->execute([$this->lockName]);
        if ((int) $lock->fetchColumn() !== 1) {
            throw new \RuntimeException(
                'Another MySQL fixture owns this schema'
            );
        }
        foreach ($this->keys as $table => $columns) {
            $count = (int) $this->pdo->query(
                'SELECT COUNT(*) FROM `' . $table . '`'
            )->fetchColumn();
            if ($count !== 0) {
                $release = $this->pdo->prepare('SELECT RELEASE_LOCK(?)');
                $release->execute([$this->lockName]);
                throw new \RuntimeException(
                    'Test table must start empty: ' . $table
                );
            }
        }
        $this->owned = true;
        $server['autocommit'] = '1';
        $server['persistent'] = '0';
        $this->dataSource = new DataSource($server);
        $this->connection = new Connection();
        $this->connection->connect($this->dataSource);
        $this->executor = new SqlExecutor($this->connection);
    }

    /**
     * Gets the executor connected to the actual MySQL test server
     *
     * @return SqlExecutor Real application executor in autocommit mode
     */
    public function getExecutor(): SqlExecutor
    {
        return $this->executor;
    }

    /**
     * Opens an independent connection for observing real lock contention
     *
     * All connections share this fixture's schema ownership and row cleanup.
     *
     * @return SqlExecutor Executor with a separate MySQL connection
     * @throws \Throwable If the additional connection cannot be established
     */
    public function createExecutor(): SqlExecutor
    {
        $connection = new Connection();
        $connection->connect($this->dataSource);
        $executor = new SqlExecutor($connection);
        $this->additionalConnections[] = $connection;
        $this->additionalExecutors[] = $executor;
        return $executor;
    }

    /**
     * Arranges or inspects fixture data using explicitly bound values
     *
     * @param string $sql Statement operating only on this test schema
     * @param array<int, mixed> $values Positional fixture values
     * @return \PDOStatement Executed statement for observing stored results
     */
    public function execute(string $sql, array $values = []): \PDOStatement
    {
        if (preg_match('/\b(?:DROP|TRUNCATE|ALTER|CREATE)\b/i', $sql)) {
            throw new \LogicException(
                'Fixtures must preserve the installed schema'
            );
        }
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);
        return $statement;
    }

    /**
     * Creates a test account and returns its generated identifier
     *
     * @param bool $mfa Whether the account requires MFA
     * @return int Generated account identifier
     */
    public function createUser(bool $mfa = false): int
    {
        $this->execute(
            'INSERT INTO users (display_name, mfa_required) VALUES (?, ?)',
            ['Unit test account', (int) $mfa]
        );
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Removes this case's rows and releases its schema ownership
     *
     * @return void
     * @throws \Throwable If rollback, row cleanup or lock release fails
     */
    public function close(): void
    {
        if (!$this->owned) {
            return;
        }
        $this->executor->rollback();
        foreach ($this->additionalExecutors as $executor) {
            $executor->rollback();
        }
        foreach ($this->keys as $table => $columns) {
            $fields = [];
            $conditions = [];
            foreach ($columns as $column) {
                $fields[] = '`' . $column . '`';
                $conditions[] = '`' . $column . '` = ?';
            }
            $rows = $this->pdo->query('SELECT ' . implode(', ', $fields)
                . ' FROM `' . $table . '`')->fetchAll(\PDO::FETCH_NUM);
            $delete = $this->pdo->prepare('DELETE FROM `' . $table
                . '` WHERE ' . implode(' AND ', $conditions));
            foreach ($rows as $row) {
                $delete->execute($row);
            }
        }
        $this->connection->disconnect();
        foreach ($this->additionalConnections as $connection) {
            $connection->disconnect();
        }
        $release = $this->pdo->prepare('SELECT RELEASE_LOCK(?)');
        $release->execute([$this->lockName]);
        $this->owned = false;
    }

    /** Cleans fixture rows before this owner leaves scope. */
    public function __destruct()
    {
        $this->close();
    }
}
