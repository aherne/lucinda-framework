<?php

namespace Lucinda\Project\Persistence\Redis\Scripts;

use Lucinda\Project\Persistence\Redis\Script;

/**
 * Reads session bytes while the requesting lease remains valid
 *
 * The instance retains fixed source and independently bound runtime values.
 *
 * @internal
 */
final class ReadSession extends Script
{
    /**
     * Captures the keys and values for this atomic operation
     *
     * @param string $lock Lock key
     * @param string $data Session data key
     * @param string $token Expected owner token
     */
    public function __construct(
        string $lock,
        string $data,
        string $token
    ) {
        $source = <<<'LUA'
if redis.call('GET', KEYS[1]) ~= ARGV[1] then
    return redis.error_reply('session lease expired')
end
return redis.call('GET', KEYS[2]) or ''
LUA;
        parent::__construct(
            $source,
            [$lock, $data],
            [$token]
        );
    }
}
