<?php

namespace Lucinda\Project\Persistence\Builders;

/**
 * Validates and reads typed columns from one database projection
 *
 * Required columns must be present and must match their declared type.
 * This object has no storage access and never invents missing column values.
 *
 * @internal
 */
final class Row
{
    /** @var array<string, mixed> Selected database columns */
    private array $columns;

    /**
     * Captures the columns returned for one database record
     *
     * @param array<string, mixed> $columns Selected values indexed by column
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * Reads a required string column
     *
     * @param string $key Selected column name
     * @return string Validated string value
     * @throws \UnexpectedValueException If the value is missing or not a string
     */
    public function getString(string $key): string
    {
        $this->requireColumn($key);
        if (!is_string($this->columns[$key])) {
            throw new \UnexpectedValueException(
                'Expected string column: ' . $key
            );
        }
        return $this->columns[$key];
    }

    /**
     * Reads a required integer column
     *
     * @param string $key Selected column name
     * @return int Validated integer value
     * @throws \UnexpectedValueException If the value is not an integer
     */
    public function getInteger(string $key): int
    {
        $this->requireColumn($key);
        $value = $this->columns[$key];
        if (
            (!is_int($value) && !is_string($value))
            || filter_var($value, FILTER_VALIDATE_INT) === false
        ) {
            throw new \UnexpectedValueException(
                'Expected integer column: ' . $key
            );
        }
        return (int) $value;
    }

    /**
     * Reads a numeric boolean column
     *
     * @param string $key Selected column name
     * @return bool True for one and false for zero
     * @throws \UnexpectedValueException If the value is neither zero nor one
     */
    public function getBoolean(string $key): bool
    {
        $value = $this->getInteger($key);
        if ($value !== 0 && $value !== 1) {
            throw new \UnexpectedValueException(
                'Expected boolean column: ' . $key
            );
        }
        return $value === 1;
    }

    /**
     * Reads an explicitly nullable string column
     *
     * @param string $key Selected column name
     * @return string|null String value or SQL NULL
     * @throws \UnexpectedValueException If the column is missing or malformed
     */
    public function getNullableString(string $key): ?string
    {
        $this->requireColumn($key);
        if ($this->columns[$key] === null) {
            return null;
        }
        return $this->getString($key);
    }

    /**
     * Reads an explicitly nullable integer column
     *
     * @param string $key Selected column name
     * @return int|null Integer value or SQL NULL
     * @throws \UnexpectedValueException If the column is missing or malformed
     */
    public function getNullableInteger(string $key): ?int
    {
        $this->requireColumn($key);
        if ($this->columns[$key] === null) {
            return null;
        }
        return $this->getInteger($key);
    }

    /**
     * Reads a scalar returned by a database expression
     *
     * @param string $key Selected expression alias
     * @return string String representation of an integer or string value
     * @throws \UnexpectedValueException If the value is null or nonscalar
     */
    public function getScalar(string $key): string
    {
        $this->requireColumn($key);
        $value = $this->columns[$key];
        if (!is_string($value) && !is_int($value)) {
            throw new \UnexpectedValueException('Expected scalar: ' . $key);
        }
        return (string) $value;
    }

    /**
     * Requires an explicitly selected column
     *
     * @param string $key Expected column name
     * @throws \UnexpectedValueException If the projection omits the column
     */
    private function requireColumn(string $key): void
    {
        if (!array_key_exists($key, $this->columns)) {
            throw new \UnexpectedValueException('Missing column: ' . $key);
        }
    }
}
