<?php

namespace Test\Lucinda\Project\Persistence\Builders;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Builders;
use Lucinda\Framework\Persistence;
use Lucinda\Framework\Persistence\Builders\Row;
use Lucinda\Framework\Persistence\Queries\ParametersValidator;

/** Verifies Identity projection conversion and validation. */
final class IdentityBuilderTest
{
    /**
     * Normalizes database scalar values without losing null or binary data
     *
     * @return Result[] Observed behavior assertions
     */
    public function build(): array
    {
        $results = [];
        $builder = new Builders\IdentityBuilder();
        $row = new Row([
            'user_id' => '17',
        ], new ParametersValidator());
        $entity = $builder->build($row);
        $results[] = (new Validator\Integers(
            $entity->getUserID()
        ))->assertEquals(
            17,
            "getUserID exposes the validated projection value"
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
        $builder = new Builders\IdentityBuilder();
        $values = [
            'user_id' => '17',
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
