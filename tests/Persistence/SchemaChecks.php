<?php

namespace Test\Lucinda\Project\Persistence;

use Lucinda\Framework\Persistence\SecretBox;
use Lucinda\Framework\Persistence\SqlExecutor;
use Lucinda\Framework\Persistence\SqlVendor;
use Lucinda\Migration\Status;
use Lucinda\Project\DAO;
use Lucinda\Project\Persistence\Queries\Accounts\CreateAccount;
use Lucinda\SQL\Connection;
use Lucinda\SQL\DataSource;

/** Executes a fresh vendor schema and its DAO contracts in isolated storage. */
final class SchemaChecks
{
    /** Native connection used only for arranging and observing test fixtures */
    private \PDO $pdo;

    /** Real framework executor sharing the fixture connection */
    private SqlExecutor $sql;

    /** Dialect whose installation file is under test */
    private SqlVendor $vendor;

    /** Number of assertions completed by this instance */
    private int $count = 0;

    /**
     * Retains an empty isolated database and wraps its actual connection
     *
     * @param \PDO $pdo Private SQLite memory or prefixed test database
     * @param SqlVendor $vendor Dialect of the supplied connection
     */
    public function __construct(\PDO $pdo, SqlVendor $vendor)
    {
        $this->pdo = $pdo;
        $this->vendor = $vendor;
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $xml = new \SimpleXMLElement('<server/>');
        $xml->addAttribute('driver', $driver);
        $xml->addAttribute('host', 'localhost');
        $xml->addAttribute('username', 'test');
        $xml->addAttribute('file', ':memory:');
        $connection = new Connection();
        // Retain the same in-memory database and its connection-local settings.
        (new \ReflectionProperty(Connection::class, 'pdo'))->setValue(
            $connection, $pdo
        );
        (new \ReflectionProperty(Connection::class, 'dataSource'))->setValue(
            $connection, new DataSource($xml)
        );
        $this->sql = new SqlExecutor($connection);
    }

    /**
     * Requires an observed database invariant
     *
     * @param bool $condition Observed assertion result
     * @param string $message Description of a failed invariant
     */
    private function check(bool $condition, string $message): void
    {
        ++$this->count;
        if (!$condition) {
            throw new \RuntimeException($this->vendor->name . ': ' . $message);
        }
    }

    /**
     * Installs the real schema and verifies its keys, defaults and DAO behavior
     *
     * @return int Number of completed assertions
     */
    public function run(): int
    {
        $cache = new DAO\SQLMigrationCache($this->sql);
        $this->check(!$cache->exists(), 'Fixture must start without tables');
        $schema = dirname(__DIR__, 2) . '/db-'
            . strtolower($this->vendor->name) . '.sql';
        $this->pdo->exec(file_get_contents($schema));
        $this->check($cache->exists(), 'Migration table was not discovered');
        $tables = [
            'users', 'user_credentials', 'oauth2_identities',
            'oauth2_approvals', 'oauth2_states', 'resources', 'roles',
            'users_roles', 'users_resources', 'roles_resources',
            'authentication_throttles', 'user_mfa', 'sessions', 'migrations',
        ];
        foreach ($tables as $table) {
            $this->check(
                (int) $this->pdo->query('SELECT COUNT(*) FROM ' . $table)
                    ->fetchColumn() === 0,
                'Installation contains missing or seeded tables'
            );
        }
        $result = $this->sql->write(new CreateAccount('?va', ''));
        $id = $result->getInsertId();
        $this->check($id > 0, 'Identity generation failed');
        $createdAt = $this->pdo->query('SELECT created_at FROM users')
            ->fetchColumn();
        $createdTime = new \DateTimeImmutable(
            $createdAt, new \DateTimeZone('UTC')
        );
        $serverTime = new \DateTimeImmutable(
            $this->pdo->query('SELECT CURRENT_TIMESTAMP')->fetchColumn(),
            new \DateTimeZone('UTC')
        );
        $this->check(
            $this->pdo->query('SELECT email FROM users')->fetchColumn() === null
                && abs($serverTime->getTimestamp()
                    - $createdTime->getTimestamp()) < 60,
            'Nullable profile or timestamp default changed'
        );
        $password = password_hash('secret', PASSWORD_DEFAULT);
        $statement = $this->pdo->prepare(
            'INSERT INTO user_credentials (user_id, username, password_hash) VALUES (?, ?, ?)'
        );
        $statement->execute([$id, 'CaseSensitive', $password]);
        $login = new DAO\FormLogin($this->sql);
        $this->check(
            $login->login('CaseSensitive', 'secret') === $id
                && $login->login('casesensitive', 'secret') === null,
            'Credential comparison or active flag is incompatible'
        );
        $this->pdo->exec("INSERT INTO resources (url, is_public) VALUES ('public', TRUE)");
        $pageID = (int) $this->pdo->lastInsertId();
        $this->pdo->exec("INSERT INTO roles (name) VALUES ('EDITORS')");
        $roleID = (int) $this->pdo->lastInsertId();
        $statement = $this->pdo->prepare(
            'INSERT INTO users_roles (user_id, role_id) VALUES (?, ?)'
        );
        $statement->execute([$id, $roleID]);
        $statement = $this->pdo->prepare(
            "INSERT INTO users_resources (user_id, resource_id, http_method) VALUES (?, ?, 'GET')"
        );
        $statement->execute([$id, $pageID]);
        $statement = $this->pdo->prepare(
            "INSERT INTO roles_resources (role_id, resource_id, http_method) VALUES (?, ?, '*')"
        );
        $statement->execute([$roleID, $pageID]);
        $pages = new DAO\PageAuthorization($this->sql);
        $this->check(
            $pages->getID('public') === $pageID && $pages->isPublic($pageID)
                && $pages->getID('Public') === null,
            'Resource identity, boolean policy or case comparison failed'
        );
        $this->check(
            (new DAO\UserRoles($this->sql))->getRoles($id) === ['EDITORS'],
            'Role projection failed'
        );
        $rights = new DAO\UserAuthorization($this->sql);
        $this->check(
            $rights->isAllowed($id, $pageID, 'GET')
                && !$rights->isAllowed($id, $pageID, 'POST')
                && (new DAO\RoleAuthorization($this->sql))
                    ->isAllowed($id, $pageID, 'POST'),
            'Direct or role-based method authorization failed'
        );
        $box = new SecretBox(base64_encode(random_bytes(32)));
        $mfa = new DAO\MultiFactorAuthentication($this->sql, $box);
        $this->check(
            !$mfa->isRequired($id) && $mfa->getAccountName($id) === (string) $id,
            'MFA policy default or numeric account label is incorrect'
        );
        $mfa->saveSetupSecret($id, 'JBSWY3DPEHPK3PXP');
        $this->check(
            $mfa->getSetupSecret($id) === 'JBSWY3DPEHPK3PXP'
                && $mfa->consumeTotpCounter($id, 123455),
            'Enrollment setup or counter consumption failed'
        );
        $mfa->enable($id, 'JBSWY3DPEHPK3PXP');
        $this->check(
            $mfa->getSecret($id) === 'JBSWY3DPEHPK3PXP'
                && !$mfa->consumeTotpCounter($id, 123455),
            'Confirmed enrollment or replay prevention failed'
        );
        $session = new DAO\SQLSessionHandler($this->sql);
        foreach (['', "binary\0\xff\\bytes"] as $bytes) {
            $session->write('opaque-session', $bytes);
            $this->check(
                $session->read('opaque-session') === $bytes,
                'Session payload bytes changed during an upsert'
            );
        }
        $states = new DAO\OAuth2States($this->sql);
        $proof = str_repeat('a', 64);
        $states->save('CaseSensitive', 'Google', time() + 60, $proof);
        $this->check(
            !$states->consume('casesensitive', 'Google', $proof)
                && !$states->consume('CaseSensitive ', 'Google', $proof)
                && $states->consume('CaseSensitive', 'Google', $proof)
                && !$states->consume('CaseSensitive', 'Google', $proof),
            'State comparison or single-use consumption failed'
        );
        $oauth = new DAO\OAuth2AutomaticProvisioning($this->sql);
        $user = new ProviderUser('remote\\id ');
        $created = $oauth->create($user, 'Google');
        $this->check(
            $created !== null && $created === $oauth->resolve($user, 'Google')
                && $oauth->resolve(new ProviderUser('remote\\id'), 'Google')
                    === null,
            'Identity comparison or transactional provisioning failed'
        );
        $approvals = new DAO\OAuth2ApprovalProvisioning($this->sql);
        $approvals->requestApproval(new ProviderUser('approval'), 'Google');
        $this->pdo->exec("UPDATE oauth2_approvals SET status = 'rejected'");
        $this->check(
            $approvals->requestApproval(new ProviderUser('approval'), 'Google')
                === \Lucinda\WebSecurity\OAuth2ApprovalStatus::REJECTED,
            'Duplicate approval replaced an existing decision'
        );
        foreach ([Status::PENDING, Status::FAILED, Status::PASSED] as $status) {
            $cache->add('Example', $status);
            $this->check(
                $cache->read()['Example'] === $status,
                'Migration status upsert failed'
            );
        }
        $throttle = new DAO\SQLLoginThrottler($this->sql, 2, 60);
        $throttle->penalize('name', '127.0.0.1');
        $this->check(
            !$throttle->isThrottled('name', '127.0.0.1'),
            'Throttle blocked before its threshold'
        );
        $throttle->penalize('name', '127.0.0.1');
        $this->check(
            $throttle->isThrottled('name', '127.0.0.1'),
            'Atomic throttle upsert lost an attempt'
        );
        $rejected = false;
        try {
            $this->pdo->exec("UPDATE migrations SET status_code = 99");
        } catch (\PDOException) {
            $rejected = true;
        }
        $this->check($rejected, 'Invalid migration status was accepted');
        $rejected = false;
        try {
            $this->pdo->exec('INSERT INTO user_mfa (user_id) VALUES (999999)');
        } catch (\PDOException) {
            $rejected = true;
        }
        $this->check($rejected, 'Foreign key enforcement is disabled');
        $statement = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        $statement->execute([$id]);
        $this->check(
            (int) $this->pdo->query('SELECT COUNT(*) FROM user_credentials')
                ->fetchColumn() === 0
                && (int) $this->pdo->query('SELECT COUNT(*) FROM user_mfa')
                    ->fetchColumn() === 0
                && (int) $this->pdo->query('SELECT COUNT(*) FROM users_roles')
                    ->fetchColumn() === 0
                && (int) $this->pdo->query('SELECT COUNT(*) FROM users_resources')
                    ->fetchColumn() === 0,
            'Account deletion did not cascade to credentials and enrollment'
        );
        return $this->count;
    }
}
