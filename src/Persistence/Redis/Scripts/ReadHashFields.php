<?php

namespace Lucinda\Project\Persistence\Redis\Scripts;

use Lucinda\Project\Persistence\Redis\Script;

/**
 * Reads all fields from one namespaced hash
 *
 * The instance retains fixed source and independently bound runtime values.
 *
 * @internal
 */
final class ReadHashFields extends Script
{
    /**
     * Captures the keys and values for this atomic operation
     *
     * @param string $key Hash storage key
     */
    public function __construct(
        string $key
    ) {
        $source = <<<'LUA'
return redis.call('HGETALL', KEYS[1])
LUA;
        parent::__construct(
            $source,
            [$key],
            []
        );
    }
}
