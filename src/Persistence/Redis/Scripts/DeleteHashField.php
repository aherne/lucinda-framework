<?php

namespace Lucinda\Project\Persistence\Redis\Scripts;

use Lucinda\Project\Persistence\Redis\Script;

/**
 * Deletes one hash field without replacing other records
 *
 * The instance retains fixed source and independently bound runtime values.
 *
 * @internal
 */
final class DeleteHashField extends Script
{
    /**
     * Captures the keys and values for this atomic operation
     *
     * @param string $key Hash storage key
     * @param string $field Record field name
     */
    public function __construct(
        string $key,
        string $field
    ) {
        $source = <<<'LUA'
return redis.call('HDEL', KEYS[1], ARGV[1])
LUA;
        parent::__construct(
            $source,
            [$key],
            [$field]
        );
    }
}
