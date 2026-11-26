<?php

namespace Test\Lucinda\Project\Persistence\Builders;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Builders;
use Lucinda\Framework\Persistence;
use Lucinda\Framework\Persistence\Builders\Row;
use Lucinda\Framework\Persistence\Queries\ParametersValidator;

/** Verifies Migration projection conversion and validation. */
final class MigrationBuilderTest
{
    /**
     * Normalizes database scalar values without losing null or binary data
     *
     * @return Result[] Observed behavior assertions
     */
    public function build(): array
    {
        $results = [];
        $builder = new Builders\MigrationBuilder();
        $row = new Row([
            'class_name' => 'App\\Migration',
            'status_code' => '2',
        ], new ParametersValidator());
        $entity = $builder->build($row);
        $results[] = (new Validator\Strings(
            $entity->getClassName()
        ))->assertEquals(
            'App\\Migration',
            "getClassName exposes the validated projection value"
        );
        $results[] = (new Validator\Arrays(
            [$entity->getStatus()]
        ))->assertIdentical(
            [\Lucinda\Migration\Status::FAILED],
            "getStatus exposes the validated projection value"
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
        $builder = new Builders\MigrationBuilder();
        $values = [
            'class_name' => 'App\\Migration',
            'status_code' => '2',
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

    /**
     * Maps all known migration statuses and rejects unknown stored codes
     *
     * @return Result[] Observed behavior assertions
     */
    public function testStatusMapping(): array
    {
        $results = [];
        $builder = new Builders\MigrationBuilder();
        foreach (
            [
                \Lucinda\Migration\Status::PENDING,
                \Lucinda\Migration\Status::FAILED,
                \Lucinda\Migration\Status::PASSED,
            ] as $status
        ) {
            $entity = $builder->build(new Row([
                "class_name" => "Example", "status_code" => $status->value,
            ], new ParametersValidator()));
            $results[] = (new Validator\Arrays(
                [$entity->getStatus()]
            ))->assertIdentical(
                [$status],
                "Every persisted migration status maps to its enum case"
            );
        }
        $rejected = false;
        try {
            $builder->build(new Row([
                "class_name" => "Example", "status_code" => 99,
            ], new ParametersValidator()));
        } catch (\UnexpectedValueException) {
            $rejected = true;
        }
        $results[] = (new Validator\Booleans(
            $rejected
        ))->assertTrue(
            "Unknown migration statuses fail explicitly"
        );
        return $results;
    }
}
