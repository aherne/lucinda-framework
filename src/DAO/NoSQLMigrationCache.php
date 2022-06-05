<?php

namespace Lucinda\Project\DAO;

use Lucinda\Migration\Status;
use Lucinda\NoSQL\Driver;

/**
 * Saves migration progress in an NoSQL key-value store
 */
class NoSQLMigrationCache implements \Lucinda\Migration\Cache
{
    public const DRIVER_NAME = "";
    private string $keyName;
    private Driver $connection;

    /**
     * Sets key name
     *
     * @param string $keyName
     */
    public function __construct(string $keyName = "migrations")
    {
        $this->keyName = $keyName;
        $this->connection = \NoSQL(self::DRIVER_NAME);
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Migration\Cache::exists()
     */
    public function exists(): bool
    {
        return $this->connection->contains($this->keyName);
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Migration\Cache::create()
     */
    public function create(): void
    {
        $this->connection->set($this->keyName, json_encode([]));
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Migration\Cache::add()
     */
    public function add(string $className, Status $statusCode): void
    {
        $isSuccessful = ($statusCode==Status::PASSED);
        $items = json_decode($this->connection->get($this->keyName), true);
        if (isset($items[$className]) && ($items[$className]["is_successful"] == $isSuccessful)) {
            return;
        }
        $items[$className] = ["is_successful"=>$isSuccessful, "date"=>date("Y-m-d H:i:s")];
        $this->connection->set($this->keyName, json_encode($items));
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Migration\Cache::read()
     */
    public function read(): array
    {
        $items = json_decode($this->connection->get($this->keyName), true);
        $results = [];
        foreach ($items as $className => $details) {
            $results[$className] = ($details["is_successful"] ? Status::PASSED : Status::FAILED);
        }
        return $results;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Migration\Cache::remove()
     */
    public function remove(string $className): void
    {
        $items = json_decode($this->connection->get($this->keyName), true);
        unset($items[$className]);
        $this->connection->set($this->keyName, json_encode($items));
    }
}
