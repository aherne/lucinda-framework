<?php

namespace Lucinda\Project\Persistence\Redis\Scripts;

use Lucinda\Project\Persistence\Redis\Script;

/**
 * Reads a failure counter without creating a window
 *
 * The instance retains fixed source and independently bound runtime values.
 *
 * @internal
 */
final class ReadCounter extends Script
{
    /**
     * Captures the keys and values for this atomic operation
     *
     * @param string $key Counter key
     */
    public function __construct(
        string $key
    ) {
        $source = <<<'LUA'
return tonumber(redis.call('GET', KEYS[1]) or '0')
LUA;
        parent::__construct(
            $source,
            [$key],
            []
        );
    }
}
