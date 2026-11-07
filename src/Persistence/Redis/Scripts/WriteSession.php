<?php

namespace Lucinda\Project\Persistence\Redis\Scripts;

use Lucinda\Project\Persistence\Redis\Script;

/**
 * Writes session bytes only while the current lease is owned
 *
 * The instance retains fixed source and independently bound runtime values.
 *
 * @internal
 */
final class WriteSession extends Script
{
    /**
     * Captures the keys and values for this atomic operation
     *
     * @param string $lock Lock key
     * @param string $data Session data key
     * @param string $token Expected owner token
     * @param string $payload Serialized session bytes
     * @param int $ttl Session lifetime in seconds
     */
    public function __construct(
        string $lock,
        string $data,
        string $token,
        string $payload,
        int $ttl
    ) {
        $source = <<<'LUA'
if redis.call('GET', KEYS[1]) ~= ARGV[1] then
    return redis.error_reply('session lease expired')
end
redis.call('SET', KEYS[2], ARGV[2], 'EX', ARGV[3])
return 1
LUA;
        parent::__construct(
            $source,
            [$lock, $data],
            [$token, $payload, $ttl]
        );
    }
}
