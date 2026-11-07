<?php

namespace Lucinda\Project\Persistence\Redis\Scripts;

use Lucinda\Project\Persistence\Redis\Script;

/**
 * Releases a session lease only for its current owner
 *
 * The instance retains fixed source and independently bound runtime values.
 *
 * @internal
 */
final class ReleaseLease extends Script
{
    /**
     * Captures the keys and values for this atomic operation
     *
     * @param string $key Lock key
     * @param string $token Expected owner token
     */
    public function __construct(
        string $key,
        string $token
    ) {
        $source = <<<'LUA'
if redis.call('GET', KEYS[1]) == ARGV[1] then
    return redis.call('DEL', KEYS[1])
end
return 0
LUA;
        parent::__construct(
            $source,
            [$key],
            [$token]
        );
    }
}
