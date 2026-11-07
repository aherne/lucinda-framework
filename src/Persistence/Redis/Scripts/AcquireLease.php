<?php

namespace Lucinda\Project\Persistence\Redis\Scripts;

use Lucinda\Project\Persistence\Redis\Script;

/**
 * Acquires a session lease only when no owner exists
 *
 * The instance retains fixed source and independently bound runtime values.
 *
 * @internal
 */
final class AcquireLease extends Script
{
    /**
     * Captures the keys and values for this atomic operation
     *
     * @param string $key Lock key
     * @param string $token Unique owner token
     * @param int $seconds Lease lifetime in seconds
     */
    public function __construct(
        string $key,
        string $token,
        int $seconds
    ) {
        $source = <<<'LUA'
local acquired = redis.call('SET', KEYS[1], ARGV[1],
    'NX', 'EX', ARGV[2])
if acquired then
    return 1
end
return 0
LUA;
        parent::__construct(
            $source,
            [$key],
            [$token, $seconds]
        );
    }
}
