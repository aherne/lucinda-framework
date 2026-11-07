<?php

namespace Lucinda\Project\Persistence\Redis\Scripts;

use Lucinda\Project\Persistence\Redis\Script;

/**
 * Writes one hash field independently from other records
 *
 * The instance retains fixed source and independently bound runtime values.
 *
 * @internal
 */
final class WriteHashField extends Script
{
    /**
     * Captures the keys and values for this atomic operation
     *
     * @param string $key Hash storage key
     * @param string $field Record field name
     * @param string $value Record value
     */
    public function __construct(
        string $key,
        string $field,
        string $value
    ) {
        $source = <<<'LUA'
return redis.call('HSET', KEYS[1], ARGV[1], ARGV[2])
LUA;
        parent::__construct(
            $source,
            [$key],
            [$field, $value]
        );
    }
}
