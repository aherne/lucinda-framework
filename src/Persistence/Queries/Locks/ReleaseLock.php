<?php

namespace Lucinda\Project\Persistence\Queries\Locks;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Releases the connection-owned lock of one protected operation
 *
 * Owns the generated statement and bound lock name for this operation.
 *
 * @internal
 */
final class ReleaseLock extends Query
{
    /**
     * Builds the advisory lock operation for this identity
     *
     * @param string $name Hashed MySQL advisory lock name
     */
    public function __construct(string $name)
    {
        $this->setParameters($name);
        $this->statement = new Select('DUAL');
        $this->setFields();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $name Hashed MySQL advisory lock name
     */
    private function setParameters(
        string $name
    ): void {
        $this->parameters = [':name' => $name];
    }

    /**
     * Defines selected columns and expression aliases
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setFields(): void
    {
        $fields = $this->statement->fields();
        $fields->add('RELEASE_LOCK(:name)', 'value');
    }
}
