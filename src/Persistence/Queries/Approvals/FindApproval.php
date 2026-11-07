<?php

namespace Lucinda\Project\Persistence\Queries\Approvals;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Reads the decision for one provider identity
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class FindApproval extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $vendor Configured OAuth2 provider name
     * @param string $remote Provider-assigned identity identifier
     */
    public function __construct(
        string $vendor,
        string $remote
    ) {
        $this->setParameters($vendor, $remote);
        $this->statement = new Select('oauth2_approvals');
        $this->setFields();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $vendor Configured OAuth2 provider name
     * @param string $remote Provider-assigned identity identifier
     */
    private function setParameters(
        string $vendor,
        string $remote
    ): void {
        $this->parameters = [':vendor' => $vendor, ':remote' => $remote];
    }

    /**
     * Defines selected columns and expression aliases
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setFields(): void
    {
        $this->statement->fields(['status']);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $this->statement->where([
            'vendor_name' => ':vendor',
            'remote_id' => ':remote',
        ]);
    }
}
