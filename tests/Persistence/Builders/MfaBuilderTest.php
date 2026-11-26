<?php

namespace Test\Lucinda\Project\Persistence\Builders;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Builders;
use Lucinda\Framework\Persistence;
use Lucinda\Framework\Persistence\Builders\Row;
use Lucinda\Framework\Persistence\Queries\ParametersValidator;

/** Verifies Mfa projection conversion and validation. */
final class MfaBuilderTest
{
    /**
     * Normalizes database scalar values without losing null or binary data
     *
     * @return Result[] Observed behavior assertions
     */
    public function build(): array
    {
        $results = [];
        $builder = new Builders\MfaBuilder();
        $row = new Row([
            'mfa_required' => '1',
            'account_name' => 'account@example.test',
            'secret' => null,
            'setup_secret' => 'encrypted-setup',
            'setup_expires' => '1700000000',
            'last_counter' => '0',
        ], new ParametersValidator());
        $entity = $builder->build($row);
        $results[] = (new Validator\Booleans(
            $entity->isRequired() === true
        ))->assertTrue(
            "isRequired exposes the validated projection value"
        );
        $results[] = (new Validator\Strings(
            $entity->getAccountName()
        ))->assertEquals(
            'account@example.test',
            "getAccountName exposes the validated projection value"
        );
        $results[] = (new Validator\Booleans(
            $entity->getSecret() === null
        ))->assertTrue(
            "getSecret exposes the validated projection value"
        );
        $results[] = (new Validator\Strings(
            $entity->getSetupSecret()
        ))->assertEquals(
            'encrypted-setup',
            "getSetupSecret exposes the validated projection value"
        );
        $results[] = (new Validator\Integers(
            $entity->getSetupExpires()
        ))->assertEquals(
            1700000000,
            "getSetupExpires exposes the validated projection value"
        );
        $results[] = (new Validator\Integers(
            $entity->getCounter()
        ))->assertEquals(
            0,
            "getCounter exposes the validated projection value"
        );
        return $results;
    }

    /**
     * Rejects missing and malformed values for every selected column
     *
     * @return Result[] Observed behavior assertions
     */
    public function testInvalidProjection(): array
    {
        $results = [];
        $builder = new Builders\MfaBuilder();
        $values = [
            'mfa_required' => '1',
            'account_name' => 'account@example.test',
            'secret' => null,
            'setup_secret' => 'encrypted-setup',
            'setup_expires' => '1700000000',
            'last_counter' => '0',
        ];
        foreach (array_keys($values) as $column) {
            $missing = $values;
            unset($missing[$column]);
            $rejected = false;
            try {
                $builder->build(new Row($missing, new ParametersValidator()));
            } catch (Persistence\Queries\ValidationException) {
                $rejected = true;
            }
            $results[] = (new Validator\Booleans(
                $rejected
            ))->assertTrue(
                "Missing column is rejected: " . $column
            );
            $invalid = $values;
            $invalid[$column] = [];
            $rejected = false;
            try {
                $builder->build(new Row($invalid, new ParametersValidator()));
            } catch (Persistence\Queries\ValidationException) {
                $rejected = true;
            }
            $results[] = (new Validator\Booleans(
                $rejected
            ))->assertTrue(
                "Malformed column is rejected: " . $column
            );
        }
        return $results;
    }
}
