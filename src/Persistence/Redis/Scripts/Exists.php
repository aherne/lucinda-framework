<?php

namespace Lucinda\Project\Persistence\Redis\Scripts;

use Lucinda\Project\Persistence\Redis\Script;

/**
 * Checks whether one namespaced storage key exists
 *
 * The instance retains fixed source and independently bound runtime values.
 *
 * @internal
 */
final class Exists extends Script
{
    /**
     * Captures the keys and values for this atomic operation
     *
     * @param string $key Storage key
     */
    public function __construct(
        string $key
    ) {
        $source = <<<'LUA'
return redis.call('EXISTS', KEYS[1])
LUA;
        parent::__construct(
            $source,
            [$key],
            []
        );
    }
}
