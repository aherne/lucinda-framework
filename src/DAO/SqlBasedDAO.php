<?php

namespace Lucinda\Project\DAO;

use Lucinda\Framework\Persistence\SqlExecutor;

/**
 * Retains the injected SQL executor shared by each DAO's operations
 *
 * The caller owns connection selection and shares the same executor when a
 * transaction or advisory lock must cover multiple persistence operations.
 */
abstract class SqlBasedDAO
{
    /** Executor retained for this DAO's entire lifecycle */
    protected SqlExecutor $sql;

    /**
     * Captures the executor used by all SQL operations in this DAO
     *
     * @param SqlExecutor $executor Explicitly selected connection owner
     */
    public function __construct(SqlExecutor $executor)
    {
        $this->sql = $executor;
    }
}