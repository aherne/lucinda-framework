<?php

namespace Lucinda\Project\Persistence\Queries\States;

use Lucinda\Framework\Persistence\Queries\Query;
use Lucinda\Framework\Persistence\Queries\Parameters;
use Lucinda\SQL\DataType;
use Lucinda\Query\Delete;
use Lucinda\Query\Operator\Comparison as C;

/**
 * Deletes exactly one live matching authorization attempt
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class ConsumeState extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $state Random authorization state sent to the provider
     * @param string $vendor Configured OAuth2 provider name
     * @param string $proof Hexadecimal SHA-256 browser proof hash
     * @param int $now Current Unix timestamp
     */
    public function __construct(
        string $state,
        string $vendor,
        string $proof,
        int $now
    ) {
        $this->setParameters($state, $vendor, $proof, $now);
        $this->statement = new Delete('oauth2_states');
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $state Random authorization state sent to the provider
     * @param string $vendor Configured OAuth2 provider name
     * @param string $proof Hexadecimal SHA-256 browser proof hash
     * @param int $now Current Unix timestamp
     */
    private function setParameters(
        string $state,
        string $vendor,
        string $proof,
        int $now
    ): void {
        $this->parameters = new Parameters();
        $this->parameters->set(':state', $state, DataType::STRING);
        $this->parameters->set(':vendor', $vendor, DataType::STRING);
        $this->parameters->set(':proof', $proof, DataType::STRING);
        $this->parameters->set(':now', $now, DataType::INTEGER);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $this->statement->where([
            'state' => ':state',
            'vendor_name' => ':vendor',
            'browser_proof_hash' => ':proof',
        ])->set('valid_until', ':now', C::GREATER);
    }
}
