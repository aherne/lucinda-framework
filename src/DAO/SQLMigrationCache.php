<?php

namespace Lucinda\Project\DAO;

use Lucinda\SQL\DataType;
use Lucinda\Migration\Cache;
use Lucinda\Migration\Status;
use Lucinda\Project\Persistence\Builders\MigrationBuilder;
use Lucinda\Project\Persistence\Queries\Migrations\DeleteMigration;
use Lucinda\Project\Persistence\Queries\Migrations\FindMigrationTable;
use Lucinda\Project\Persistence\Queries\Migrations\ReadMigrations;
use Lucinda\Project\Persistence\Queries\Migrations\SaveMigration;

/**
 * Persists migration execution statuses in the configured SQL schema
 *
 * The schema must be installed before use. Repeated status writes use
 * an upsert and preserve pending, failed and passed results distinctly.
 */
final class SQLMigrationCache extends SqlBasedDAO implements Cache
{
    /**
     * Checks whether the application's migration table has been installed
     *
     * @return bool True when migration storage exists in the current schema
     * @throws \Throwable If schema metadata cannot be read
     */
    public function exists(): bool
    {
        $selection = $this->sql->read(new FindMigrationTable(
            $this->sql->getVendor()
        ));
        return $selection->toValue(DataType::INTEGER) !== null;
    }

    /**
     * Requires manually installed storage before running migrations
     *
     * @throws \RuntimeException If migration storage has not been installed
     * @throws \Throwable If schema metadata cannot be read
     */
    public function create(): void
    {
        if (!$this->exists()) {
            throw new \RuntimeException(
                'Install the migrations table before running migrations'
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
        $query = new SaveMigration(
            $this->sql->getVendor(),
            $className,
            $statusCode->value
        );
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
        $selection = $this->sql->read(new ReadMigrations());
        $statuses = [];
        $builder = new MigrationBuilder();
        while (($row = $selection->toRow()) !== null) {
            $migration = $builder->build($row);
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
