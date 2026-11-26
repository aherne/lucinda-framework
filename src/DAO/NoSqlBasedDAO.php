<?php

namespace Lucinda\Project\DAO;

use Lucinda\Framework\Persistence\NoSqlExecutor;

/**
 * Retains the injected NoSQL executor shared by each DAO's operations
 *
 * The caller selects the atomic driver and namespace before constructing the
 * DAO. Child classes coordinate their operation-specific storage objects.
 */
abstract class NoSqlBasedDAO
{
    /**
     * Executor retained for this DAO's entire lifecycle
     */
    protected NoSqlExecutor $executor;

    /**
     * Captures the executor used by this DAO's storage operations
     *
     * @param NoSqlExecutor $executor Explicitly selected atomic storage owner
     */
    public function __construct(NoSqlExecutor $executor)
    {
        $this->executor = $executor;
    }
}
