<?php

namespace Lucinda\Project\DAO;

use Lucinda\Migration\Cache;
use Lucinda\Migration\Status;
use Lucinda\Project\Persistence\Redis\MigrationStore;
use Lucinda\Project\Persistence\RedisExecutor;

/**
 * Adapts the Redis migration ledger to the migration API cache contract
 *
 * Migration-specific hash layout and status validation belong to its store.
 * The Redis executor contains no migration policy or record interpretation.
 */
final class NoSQLMigrationCache implements Cache
{
    /** Application-scoped migration ledger owned by this cache */
    private MigrationStore $store;

    /**
     * Captures the executor used by the application's migration ledger
     *
     * @param RedisExecutor|null $executor Injected Redis connection owner
     */
    public function __construct(?RedisExecutor $executor = null)
    {
        if ($executor === null) {
            $executor = new RedisExecutor();
        }
        $this->store = new MigrationStore($executor);
    }

    /**
     * Checks whether Redis contains this application's migration ledger
     *
     * @return bool True when the ledger exists
     * @throws \Throwable If Redis cannot inspect the ledger
     */
    public function exists(): bool
    {
        return $this->store->exists();
    }

    /**
     * Initializes the ledger without replacing recorded migration statuses
     *
     * @throws \Throwable If initialization fails
     */
    public function create(): void
    {
        $this->store->create();
    }

    /**
     * Records one migration independently from concurrent ledger updates
     *
     * @param string $className Fully qualified migration class name
     * @param Status $statusCode Migration execution result
     * @throws \Throwable If the status cannot be persisted
     */
    public function add(string $className, Status $statusCode): void
    {
        $this->store->save($className, $statusCode);
    }

    /**
     * Gets the application's validated migration status values
     *
     * @return array<string, Status> Statuses indexed by migration class name
     * @throws \Throwable If the ledger cannot be read or contains invalid data
     */
    public function read(): array
    {
        return $this->store->read();
    }

    /**
     * Removes one migration without replacing other ledger records
     *
     * @param string $className Fully qualified migration class name
     * @throws \Throwable If the status cannot be removed
     */
    public function remove(string $className): void
    {
        $this->store->remove($className);
    }
}
