<?php

namespace Lucinda\Project\Persistence\Redis\Scripts;

use Lucinda\Project\Persistence\Redis\Script;

/**
 * Increments a fixed failure window atomically
 *
 * The instance retains fixed source and independently bound runtime values.
 *
 * @internal
 */
final class IncrementWindow extends Script
{
    /**
     * Captures the keys and values for this atomic operation
     *
     * @param string $key Counter key
     * @param int $period Window lifetime in seconds
     */
    public function __construct(
        string $key,
        int $period
    ) {
        $source = <<<'LUA'
local n = redis.call('INCR', KEYS[1])
if n == 1 then
    redis.call('EXPIRE', KEYS[1], ARGV[1])
end
return n
LUA;
        parent::__construct(
            $source,
            [$key],
            [$period]
        );
    }
}
