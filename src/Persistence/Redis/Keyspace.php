<?php

namespace Lucinda\Project\Persistence\Redis;

/**
 * Owns the Redis key namespace for one application and environment
 *
 * Purpose-specific stores select their own domain and identity. Hashing the
 * identity keeps arbitrary input outside the structural portion of each key.
 *
 * @internal
 */
final class Keyspace
{
    /** Application and environment prefix retained for every key */
    private string $prefix;

    /**
     * Captures a nonempty Redis namespace
     *
     * @param string $prefix Application-specific key prefix
     * @throws \InvalidArgumentException If the prefix is empty
     */
    public function __construct(string $prefix)
    {
        if ($prefix === '') {
            throw new \InvalidArgumentException('Redis namespace is empty');
        }
        $this->prefix = $prefix;
    }

    /**
     * Builds one key within this application's namespace
     *
     * @param string $purpose Storage domain selected by its owning store
     * @param string $identity Identity within that storage domain
     * @return string Namespaced Redis key
     */
    public function getKey(string $purpose, string $identity): string
    {
        return $this->prefix . $purpose . ':' . hash('sha256', $identity);
    }
}
