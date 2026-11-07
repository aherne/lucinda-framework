<?php

namespace Lucinda\Project\Persistence\Redis;

use Lucinda\Project\Persistence\Redis\Scripts\RememberRepresentation;
use Lucinda\Project\Persistence\RedisExecutor;

/**
 * Owns retained first-observed timestamps for rendered representations
 *
 * The timestamp remains stable while the same representation is retained.
 * This store owns the retention policy; the executor only executes its script.
 *
 * @internal
 */
final class RepresentationStore
{
    /** Executor used for representation timestamp operations */
    private RedisExecutor $executor;

    /** Retention lifetime of one representation in seconds */
    private int $lifetime;

    /**
     * Captures the executor and representation retention policy
     *
     * @param RedisExecutor $executor Connection and namespace for this store
     * @param int $lifetime Positive retention lifetime in seconds
     * @throws \InvalidArgumentException If the lifetime is not positive
     */
    public function __construct(
        RedisExecutor $executor,
        int $lifetime = 86400
    ) {
        if ($lifetime < 1) {
            throw new \InvalidArgumentException('Invalid retention lifetime');
        }
        $this->executor = $executor;
        $this->lifetime = $lifetime;
    }

    /**
     * Gets or atomically establishes a representation's observation time
     *
     * @param string $identity Host and rendered representation identity
     * @return int Unix timestamp of the retained first observation
     * @throws \Throwable If Redis cannot retain or read the timestamp
     */
    public function getTime(string $identity): int
    {
        $key = $this->executor->getKeyspace()->getKey(
            'representation',
            $identity
        );
        $script = new RememberRepresentation($key, time(), $this->lifetime);
        $result = $this->executor->execute($script);
        return $result->getInteger();
    }
}
