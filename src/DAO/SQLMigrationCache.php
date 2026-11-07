<?php

namespace Lucinda\Project\DAO;

use Lucinda\Migration\Cache;
use Lucinda\Migration\Status;
use Lucinda\Project\Persistence\Builders\MigrationBuilder;
use Lucinda\Project\Persistence\Queries\Migrations\DeleteMigration;
use Lucinda\Project\Persistence\Queries\Migrations\FindMigrationTable;
use Lucinda\Project\Persistence\Queries\Migrations\ReadMigrations;
use Lucinda\Project\Persistence\Queries\Migrations\SaveMigration;
use Lucinda\Project\Persistence\SqlExecutor;

/**
 * Persists migration execution statuses in the application's MySQL schema
 *
 * The schema is installed manually from db.sql. Repeated status writes use
 * an upsert and preserve pending, failed and passed results distinctly.
 */
final class SQLMigrationCache implements Cache
{
    /** Executor retained for the application's migration ledger */
    private SqlExecutor $sql;

    /**
     * Captures an injected executor or a lazily configured SQL connection
     *
     * @param SqlExecutor|null $sql Executor sharing the caller's connection
     */
    public function __construct(?SqlExecutor $sql = null)
    {
        $this->sql = $sql ?? new SqlExecutor();
    }

    /**
     * Checks whether the application's migration table has been installed
     *
     * @return bool True when migration storage exists in the current schema
     * @throws \Throwable If schema metadata cannot be read
     */
    public function exists(): bool
    {
        $selection = $this->sql->select(new FindMigrationTable());
        return !$selection->isEmpty();
    }

    /**
     * Requires manually installed storage before running migrations
     *
     * @throws \RuntimeException If db.sql has not created migration storage
     * @throws \Throwable If schema metadata cannot be read
     */
    public function create(): void
    {
        if (!$this->exists()) {
            throw new \RuntimeException(
                'Install the migrations table from db.sql first'
            );
        }
    }

    /**
     * Inserts or replaces the recorded status of one migration
     *
     * @param string $className Fully qualified migration class name
     * @param Status $statusCode Migration execution result
     * @throws \Throwable If status persistence fails
     */
    public function add(string $className, Status $statusCode): void
    {
        $query = new SaveMigration($className, $statusCode->value);
        $this->sql->write($query);
    }

    /**
     * Gets outward migration statuses while retaining row entities internally
     *
     * @return array<string, Status> Statuses indexed by migration class name
     * @throws \Throwable If ledger reading or projection validation fails
     */
    public function read(): array
    {
        $selection = $this->sql->select(new ReadMigrations());
        $statuses = [];
        foreach ($selection->getRows() as $row) {
            $builder = new MigrationBuilder($row);
            $migration = $builder->build();
            $statuses[$migration->getClassName()] = $migration->getStatus();
        }
        return $statuses;
    }

    /**
     * Removes the recorded execution status of one migration
     *
     * @param string $className Fully qualified migration class name
     * @throws \Throwable If removal fails
     */
    public function remove(string $className): void
    {
        $this->sql->write(new DeleteMigration($className));
    }
}
