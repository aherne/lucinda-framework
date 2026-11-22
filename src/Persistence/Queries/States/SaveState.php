<?php

namespace Lucinda\Project\Persistence\Queries\States;

use Lucinda\Framework\Persistence\Queries\Query;
use Lucinda\Framework\Persistence\Queries\Parameters;
use Lucinda\SQL\DataType;
use Lucinda\Query\Insert;

/**
 * Inserts one browser-bound authorization attempt
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class SaveState extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $state Random authorization state sent to the provider
     * @param string $vendor Configured OAuth2 provider name
     * @param int $expires Absolute expiration in Unix seconds
     * @param string $proof Hexadecimal SHA-256 browser proof hash
     */
    public function __construct(
        string $state,
        string $vendor,
        int $expires,
        string $proof
    ) {
        $this->setParameters($state, $vendor, $expires, $proof);
        $this->statement = new Insert('oauth2_states');
        $this->setColumns();
        $this->setValues();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $state Random authorization state sent to the provider
     * @param string $vendor Configured OAuth2 provider name
     * @param int $expires Absolute expiration in Unix seconds
     * @param string $proof Hexadecimal SHA-256 browser proof hash
     */
    private function setParameters(
        string $state,
        string $vendor,
        int $expires,
        string $proof
    ): void {
        $this->parameters = new Parameters();
        $this->parameters->set(':state', $state, DataType::STRING);
        $this->parameters->set(':vendor', $vendor, DataType::STRING);
        $this->parameters->set(':expires', $expires, DataType::INTEGER);
        $this->parameters->set(':proof', $proof, DataType::STRING);
    }

    /**
     * Defines the columns written by this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setColumns(): void
    {
        $this->statement->columns([
            'state',
            'vendor_name',
            'valid_until',
            'browser_proof_hash',
        ]);
    }

    /**
     * Defines the bound value expressions for this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setValues(): void
    {
        $this->statement->values([':state', ':vendor', ':expires', ':proof']);
    }
}
