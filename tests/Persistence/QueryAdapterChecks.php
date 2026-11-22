<?php

namespace Test\Lucinda\Project\Persistence;

use Lucinda\Framework\Persistence\Queries\Query;
use Lucinda\Framework\Persistence\SqlVendor;
use Lucinda\Project\Persistence\Queries\Approvals\RequestApproval;
use Lucinda\Project\Persistence\Queries\Mfa\EnsureEnrollment;
use Lucinda\Project\Persistence\Queries\Migrations\FindMigrationTable;
use Lucinda\Project\Persistence\Queries\Migrations\SaveMigration;
use Lucinda\Project\Persistence\Queries\Sessions\WriteSession;
use Lucinda\SQL\DataType;

/** Verifies dialect dispatch, typed bindings and SQLite conflict behavior. */
final class QueryAdapterChecks
{
    /** Number of assertions completed by this test instance */
    private int $count = 0;

    /**
     * Requires an observed query or storage invariant
     *
     * @param bool $condition Observed assertion result
     * @param string $message Description of a failed invariant
     */
    private function check(bool $condition, string $message): void
    {
        ++$this->count;
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    /**
     * Checks every adapter against each supported vendor
     *
     * @return int Number of completed assertions
     */
    public function run(): int
    {
        foreach (SqlVendor::cases() as $vendor) {
            $this->checkVendor($vendor);
        }
        if (in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->checkSqlite();
        } else {
            echo "SQLite PDO execution checks skipped: pdo_sqlite unavailable.\n";
        }
        return $this->count;
    }

    /**
     * Requires correct conflict clauses and binding coverage for one dialect
     *
     * @param SqlVendor $vendor Dialect selected through the public adapter
     */
    private function checkVendor(SqlVendor $vendor): void
    {
        $operations = [
            [new WriteSession($vendor, '51', "bytes\0", 200), ['id'], false],
            [new RequestApproval($vendor, 'Google', 'remote', 'Name', ''),
                ['vendor_name', 'remote_id'], true],
            [new EnsureEnrollment($vendor, '51'), ['user_id'], true],
            [new SaveMigration($vendor, 'Example', 2), ['class_name'], false],
        ];
        foreach ($operations as [$query, $columns, $preserve]) {
            $sql = $query->getSql();
            $this->check(!$query->isSelection(), 'Upsert treated as a read');
            if ($vendor === SqlVendor::MySQL) {
                $this->check(
                    str_contains($sql, 'ON DUPLICATE KEY UPDATE')
                        && !str_contains($sql, 'ON CONFLICT'),
                    'MySQL duplicate-key clause missing'
                );
            } else {
                $target = 'ON CONFLICT (' . implode(', ', $columns) . ')';
                $this->check(
                    str_contains($sql, $target . ($preserve
                        ? ' DO NOTHING' : ' DO UPDATE SET')),
                    'Conflict target or update policy is incorrect'
                );
            }
            $this->checkBindings($query);
        }
        $query = new FindMigrationTable($vendor);
        $sql = $query->getSql();
        $this->check($query->isSelection(), 'Catalog lookup treated as a write');
        $this->checkBindings($query);
        $expression = match ($vendor) {
            SqlVendor::MySQL => 'DATABASE()',
            SqlVendor::PostgreSQL => 'current_schema()',
            SqlVendor::SQLite => 'sqlite_schema',
        };
        $this->check(
            str_contains($sql, $expression), 'Wrong schema catalog dialect'
        );
        $query = new RequestApproval($vendor, 'Google', 'remote', 'Name', '');
        $this->check(
            $query->getParameters()[':email'] === [
                'value' => null, 'type' => DataType::NULLABLE,
            ],
            'Absent approval email must bind SQL null'
        );
    }

    /**
     * Requires a typed value for every generated placeholder
     *
     * @param Query $query Operation rendered through its public adapter
     */
    private function checkBindings(Query $query): void
    {
        preg_match_all('/:[a-z_]+/', $query->getSql(), $matches);
        $placeholders = array_unique($matches[0]);
        $bindings = $query->getParameters();
        $this->check(
            count($placeholders) === count($bindings),
            'Unexpected or missing adapter bindings'
        );
        foreach ($placeholders as $name) {
            $this->check(
                isset($bindings[$name])
                    && $bindings[$name]['type'] instanceof DataType,
                'Adapter lost the explicit binding type'
            );
        }
    }

    /**
     * Executes one generated query with the operation's actual PDO types
     *
     * @param \PDO $pdo Isolated in-memory SQLite connection
     * @param Query $query Operation whose SQL and bindings are under test
     * @return \PDOStatement Executed statement for observing storage
     */
    private function execute(\PDO $pdo, Query $query): \PDOStatement
    {
        $statement = $pdo->prepare($query->getSql());
        foreach ($query->getParameters() as $name => $binding) {
            $statement->bindValue(
                $name, $binding['value'], $binding['type']->value
            );
        }
        $statement->execute();
        return $statement;
    }

    /** Requires duplicate writes to preserve or replace the intended fields. */
    private function checkSqlite(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $vendor = SqlVendor::SQLite;
        $this->check(
            $this->execute($pdo, new FindMigrationTable($vendor))
                ->fetchColumn() === false,
            'Missing migration table reported as installed'
        );
        $schema = dirname(__DIR__, 2) . '/db-sqlite.sql';
        $pdo->exec(file_get_contents($schema));
        $pdo->exec("INSERT INTO users (id, display_name) VALUES (51, 'Test')");
        $this->check(
            $this->execute($pdo, new FindMigrationTable($vendor))
                ->fetchColumn() === 1,
            'Installed migration table was not discovered'
        );
        $this->execute($pdo, new WriteSession($vendor, '51', 'old', 100));
        $this->execute($pdo, new WriteSession($vendor, '51', "new\0", 200));
        $this->check(
            $pdo->query('SELECT payload FROM sessions')->fetchColumn()
                === "new\0"
                && $pdo->query('SELECT expires FROM sessions')->fetchColumn()
                    === 200,
            'Session upsert failed to replace binary bytes and expiry'
        );
        $this->execute($pdo, new RequestApproval(
            $vendor, 'Google', 'remote', 'Original', ''
        ));
        $pdo->exec('UPDATE oauth2_approvals SET status = \'rejected\'');
        $this->execute($pdo, new RequestApproval(
            $vendor, 'Google', 'remote', 'Replacement', 'new@example.com'
        ));
        $this->check(
            $pdo->query('SELECT status FROM oauth2_approvals')->fetchColumn()
                === 'rejected'
                && $pdo->query('SELECT display_name FROM oauth2_approvals')
                    ->fetchColumn() === 'Original'
                && $pdo->query('SELECT email FROM oauth2_approvals')
                    ->fetchColumn() === null,
            'Duplicate approval replaced an existing decision or profile'
        );
        $this->execute($pdo, new EnsureEnrollment($vendor, 51));
        $pdo->exec('UPDATE user_mfa SET secret = \'keep\'');
        $this->execute($pdo, new EnsureEnrollment($vendor, 51));
        $this->check(
            $pdo->query('SELECT secret FROM user_mfa')->fetchColumn() === 'keep',
            'Ensuring an enrollment replaced an existing secret'
        );
        $this->execute($pdo, new SaveMigration($vendor, 'Example', 1));
        $this->execute($pdo, new SaveMigration($vendor, 'Example', 3));
        $this->check(
            $pdo->query('SELECT status_code FROM migrations')->fetchColumn()
                === 3
                && $pdo->query('SELECT COUNT(*) FROM migrations')
                    ->fetchColumn() === 1,
            'Migration save failed to replace exactly one status'
        );
    }
}
