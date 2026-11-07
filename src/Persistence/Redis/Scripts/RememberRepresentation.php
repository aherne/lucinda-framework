<?php

namespace Lucinda\Project\Persistence\Redis\Scripts;

use Lucinda\Project\Persistence\Redis\Script;

/**
 * Remembers the first observed time of a representation
 *
 * The instance retains fixed source and independently bound runtime values.
 *
 * @internal
 */
final class RememberRepresentation extends Script
{
    /**
     * Captures the keys and values for this atomic operation
     *
     * @param string $key Representation identity key
     * @param int $now Current Unix timestamp
     * @param int $ttl Retention lifetime in seconds
     */
    public function __construct(
        string $key,
        int $now,
        int $ttl
    ) {
        $source = <<<'LUA'
local observed = redis.call('GET', KEYS[1])
if observed then
    return observed
end
redis.call('SET', KEYS[1], ARGV[1], 'EX', ARGV[2])
return ARGV[1]
LUA;
        parent::__construct(
            $source,
            [$key],
            [$now, $ttl]
        );
    }
}
