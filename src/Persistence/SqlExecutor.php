<?php

namespace Lucinda\Project\Persistence;

use Lucinda\Framework\SqlConnectionProvider;
use Lucinda\Framework\XmlDetector;
use Lucinda\Project\Persistence\Queries\ConnectionSettings\ReadAutocommit;
use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Project\Persistence\Results\Lists;
use Lucinda\Project\Persistence\Results\Scalar;
use Lucinda\SQL\Connection;
use Lucinda\SQL\StatementResults;
use Lucinda\SQL\Transaction;

/**
 * Executes generated SQL operations on one instance-owned connection
 *
 * The caller coordinates transactions imperatively. Result projections are
 * materialized here and converted by builders outside execution. When no
 * connection is injected, configuration is loaded lazily from sql.xml.
 *
 * @internal
 */
final class SqlExecutor
{
    /** Connection used for every operation in this executor */
    private ?Connection $connection;

    /** Provider retained for the lifetime of a configured connection */
    private ?SqlConnectionProvider $provider = null;

    /** Transaction explicitly started and owned by this executor */
    private ?Transaction $transaction = null;

    /** Configured server name, empty for the single default server */
    private string $server;

    /**
     * Captures an injected connection or a lazily configured server name
     *
     * @param Connection|null $connection Shared connection for this operation
     * @param string $server Configured SQL server name
     */
    public function __construct(
        ?Connection $connection = null,
        string $server = ''
    ) {
        $this->connection = $connection;
        $this->server = $server;
    }

    /**
     * Resolves this executor's connection on first use
     *
     * @return Connection Connection retained by this executor
     * @throws \Throwable If configuration or connection setup fails
     */
    private function getConnection(): Connection
    {
        if ($this->connection === null) {
            $path = dirname(__DIR__, 2) . '/xml/stdout/sql';
            $detector = new XmlDetector($path);
            $this->provider = new SqlConnectionProvider($detector->getXML());
            $this->connection = $this->provider->getConnection($this->server);
        }
        return $this->connection;
    }

    /**
     * Prepares and executes one generated statement with typed bindings
     *
     * @param Query $query Generated operation and bound runtime values
     * @return StatementResults Driver result consumed within this executor
     * @throws \Throwable If preparation, binding or execution fails
     */
    private function execute(Query $query): StatementResults
    {
        $statement = $this->getConnection()->preparedStatement();
        $statement->prepare($query->getSql());
        foreach ($query->getParameters() as $name => $value) {
            $type = \PDO::PARAM_STR;
            if ($value === null) {
                $type = \PDO::PARAM_NULL;
                $value = '';
            } elseif (is_int($value)) {
                $type = \PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = \PDO::PARAM_BOOL;
                $value = (int) $value;
            } elseif (is_float($value)) {
                $value = (string) $value;
            }
            $statement->bind($name, $value, $type);
        }
        return $statement->execute();
    }

    /**
     * Executes a selection and captures its projections without mapping them
     *
     * @param Query $query Generated SELECT operation
     * @return Lists Internal materialized rows for explicit builders
     * @throws \LogicException If a write is supplied as a selection
     * @throws \Throwable If database access fails
     */
    public function select(Query $query): Lists
    {
        if (!$query->isSelection()) {
            throw new \LogicException('Expected a selection operation');
        }
        $result = $this->execute($query);
        return new Lists($result->toList());
    }

    /**
     * Executes a database write and captures its acceptance metadata
     *
     * @param Query $query Generated INSERT, UPDATE or DELETE operation
     * @return Scalar Affected rows and generated identifier
     * @throws \LogicException If a selection is supplied as a write
     * @throws \Throwable If database access fails
     */
    public function write(Query $query): Scalar
    {
        if ($query->isSelection()) {
            throw new \LogicException('Expected a database write operation');
        }
        $result = $this->execute($query);
        return new Scalar(
            $result->getAffectedRows(),
            $result->getInsertId()
        );
    }

    /**
     * Starts a transaction explicitly owned by the coordinating caller
     *
     * @throws \LogicException If another transaction is already active
     * @throws \Throwable If the database cannot start the transaction
     */
    public function begin(): void
    {
        $transaction = $this->getConnection()->transaction();
        if ($transaction->isActive()) {
            throw new \LogicException('Nested transactions are unsupported');
        }
        $transaction->begin();
        $this->transaction = $transaction;
    }

    /**
     * Commits the transaction started through this executor
     *
     * @throws \LogicException If this executor owns no active transaction
     * @throws \Throwable If committing fails
     */
    public function commit(): void
    {
        if ($this->transaction === null) {
            throw new \LogicException('No owned transaction to commit');
        }
        $this->transaction->commit();
        $this->transaction = null;
    }

    /**
     * Rolls back an owned transaction after a failed coordinated operation
     *
     * @throws \Throwable If the database cannot roll back
     */
    public function rollback(): void
    {
        if ($this->transaction !== null) {
            if ($this->transaction->isActive()) {
                $this->transaction->rollback();
            }
            $this->transaction = null;
        }
    }

    /**
     * Requires immediate persistence for single-use security operations
     *
     * @throws \LogicException If a transaction is active or autocommit is off
     * @throws \Throwable If the setting cannot be read
     */
    public function requireAutocommit(): void
    {
        if ($this->getConnection()->transaction()->isActive()) {
            throw new \LogicException('Operation requires no transaction');
        }
        $selection = $this->select(new ReadAutocommit());
        $row = $selection->getFirstRow();
        if ($row === null || $row->getInteger('value') !== 1) {
            throw new \LogicException('MySQL autocommit must be enabled');
        }
    }
}
