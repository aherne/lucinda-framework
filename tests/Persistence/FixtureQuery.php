<?php

namespace Test\Lucinda\Project\Persistence;

use Lucinda\Framework\Persistence\Queries\Query;
use Lucinda\Query\Delete;
use Lucinda\Query\Insert;
use Lucinda\Query\Select;
use Lucinda\Query\Update;

/** Holds a generated statement used only to arrange isolated test data. */
final class FixtureQuery extends Query
{
    /**
     * Captures one generated fixture operation and its independent bindings
     *
     * @param Select|Insert|Update|Delete $statement Generated fixture operation
     * @param array<string, mixed> $parameters Runtime values bound by the test
     */
    public function __construct(
        Select|Insert|Update|Delete $statement,
        array $parameters = []
    ) {
        $this->statement = $statement;
        $this->parameters = $parameters;
    }
}
