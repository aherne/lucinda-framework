<?php

namespace Lucinda\Project\Persistence\Queries\Locks;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Acquires a connection-owned lock for one protected operation
 *
 * Owns the generated statement and bound lock name for this operation.
 *
 * @internal
 */
final class AcquireLock extends Query
{
    /**
     * Builds the advisory lock operation for this identity
     *
     * @param string $name Hashed MySQL advisory lock name
     * @param int $timeout Maximum acquisition wait in seconds
     */
    public function __construct(string $name, int $timeout = 10)
    {
        $this->setParameters($name, $timeout);
        $this->statement = new Select('DUAL');
        $this->setFields();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $name Hashed MySQL advisory lock name
     * @param int $timeout Maximum acquisition wait in seconds
     */
    private function setParameters(
        string $name,
        int $timeout
    ): void {
        $this->parameters = [':name' => $name, ':timeout' => $timeout];
    }

    /**
     * Defines selected columns and expression aliases
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setFields(): void
    {
        $fields = $this->statement->fields();
        $fields->add('GET_LOCK(:name, :timeout)', 'value');
    }
}
