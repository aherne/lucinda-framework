<?php

namespace Lucinda\Project\Persistence\Redis;

/**
 * Validates the response returned by one completed Redis script
 *
 * Consumers request the response shape defined by their operation. Invalid
 * response types fail rather than silently becoming successful values.
 *
 * @internal
 */
final class ScriptResult
{
    /** Response owned by this completed operation */
    private mixed $value;

    /**
     * Captures the response from the execution boundary
     *
     * @param mixed $value Redis response for this script
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * Reads a script's integer response
     *
     * @return int Integer result or numeric string converted to an integer
     * @throws \UnexpectedValueException If the result is not an integer
     */
    public function getInteger(): int
    {
        if (
            (!is_int($this->value) && !is_string($this->value))
            || filter_var($this->value, FILTER_VALIDATE_INT) === false
        ) {
            throw new \UnexpectedValueException('Expected Redis integer');
        }
        return (int) $this->value;
    }

    /**
     * Reads a script's string response without changing binary payloads
     *
     * @return string Redis string value
     * @throws \UnexpectedValueException If the result is not a string
     */
    public function getString(): string
    {
        if (!is_string($this->value)) {
            throw new \UnexpectedValueException('Expected Redis string');
        }
        return $this->value;
    }

    /**
     * Reads an array returned by a script
     *
     * @return array<int, mixed> Script response elements in Redis order
     * @throws \UnexpectedValueException If the result is not an array
     */
    public function getValues(): array
    {
        if (!is_array($this->value)) {
            throw new \UnexpectedValueException('Expected Redis array');
        }
        return $this->value;
    }
}
