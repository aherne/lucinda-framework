<?php

namespace Lucinda\Project\Persistence\Redis\Scripts;

use Lucinda\Project\Persistence\Redis\Script;

/**
 * Initializes a hash without replacing its stored fields
 *
 * The instance retains fixed source and independently bound runtime values.
 *
 * @internal
 */
final class InitializeHash extends Script
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
redis.call('HSETNX', KEYS[1], '@initialized', '1')
return 1
LUA;
        parent::__construct(
            $source,
            [$key],
            []
        );
    }
}
