<?php

namespace Test\Lucinda\Project\Persistence\Builders;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Builders;
use Lucinda\Framework\Persistence;
use Lucinda\Framework\Persistence\Builders\Row;
use Lucinda\Framework\Persistence\Queries\ParametersValidator;

/** Verifies Page projection conversion and validation. */
final class PageBuilderTest
{
    /**
     * Normalizes database scalar values without losing null or binary data
     *
     * @return Result[] Observed behavior assertions
     */
    public function build(): array
    {
        $results = [];
        $builder = new Builders\PageBuilder();
        $row = new Row([
            'id' => '0',
            'is_public' => '0',
        ], new ParametersValidator());
        $entity = $builder->build($row);
        $results[] = (new Validator\Integers(
            $entity->getID()
        ))->assertEquals(
            0,
            "getID exposes the validated projection value"
        );
        $results[] = (new Validator\Booleans(
            $entity->isPublic() === false
        ))->assertTrue(
            "isPublic exposes the validated projection value"
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
        $builder = new Builders\PageBuilder();
        $values = [
            'id' => '0',
            'is_public' => '0',
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
