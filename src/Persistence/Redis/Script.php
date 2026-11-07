<?php

namespace Lucinda\Project\Persistence\Redis;

/**
 * Owns one atomic Redis script together with its keys and argument values
 *
 * Concrete script classes define one storage operation. This object carries
 * values to the execution boundary without exposing the Redis client.
 *
 * @internal
 */
abstract class Script
{
    /** Script source defining this operation */
    private string $source;

    /** @var list<string> Redis keys accessed by the operation */
    private array $keys;

    /** @var list<int|string> Argument values bound separately from source */
    private array $arguments;

    /**
     * Captures one atomic operation and its independent runtime values
     *
     * @param string $source Fixed Lua script source
     * @param list<string> $keys Keys accessed by this operation
     * @param list<int|string> $arguments Bound runtime arguments
     */
    protected function __construct(
        string $source,
        array $keys,
        array $arguments = []
    ) {
        $this->source = $source;
        $this->keys = $keys;
        $this->arguments = $arguments;
    }

    /**
     * Gets the script source executed atomically by Redis
     *
     * @return string Lua source without interpolated runtime values
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Gets keys followed by bound script arguments
     *
     * @return list<int|string> Parameters in Redis EVAL order
     */
    public function getParameters(): array
    {
        $parameters = $this->keys;
        foreach ($this->arguments as $argument) {
            $parameters[] = $argument;
        }
        return $parameters;
    }

    /**
     * Gets the number of leading parameters interpreted as keys
     *
     * @return int Number of Redis keys
     */
    public function getKeyCount(): int
    {
        return count($this->keys);
    }
}
