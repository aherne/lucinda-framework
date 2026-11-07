<?php

namespace Lucinda\Project\Persistence\Results;

use Lucinda\Project\Persistence\Builders\Row;

/**
 * Owns the selected projections returned by one database operation
 *
 * Rows remain internal to persistence and are explicitly passed to builders.
 * No database connection or live result cursor escapes the executor.
 *
 * @internal
 */
final class Lists
{
    /** @var list<Row> Selected projections in database order */
    private array $rows = [];

    /**
     * Captures the materialized rows from one selection
     *
     * @param list<array<string, mixed>> $rows Database projections
     */
    public function __construct(array $rows)
    {
        foreach ($rows as $columns) {
            $this->rows[] = new Row($columns);
        }
    }

    /**
     * Gets the first selected projection when one exists
     *
     * @return Row|null First row or null for an empty selection
     */
    public function getFirstRow(): ?Row
    {
        if ($this->isEmpty()) {
            return null;
        }
        return $this->rows[0];
    }

    /**
     * Gets the selected projections in database order
     *
     * @return list<Row> Internal rows to convert through builders
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * Checks whether the query selected any records
     *
     * @return bool True when no row matched the query
     */
    public function isEmpty(): bool
    {
        return count($this->rows) === 0;
    }
}
