<?php

namespace Test\Lucinda\Project\Persistence\Builders;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Builders;
use Lucinda\Framework\Persistence;
use Lucinda\Framework\Persistence\Builders\Row;
use Lucinda\Framework\Persistence\Queries\ParametersValidator;

/** Verifies SessionData projection conversion and validation. */
final class SessionDataBuilderTest
{
    /**
     * Normalizes database scalar values without losing null or binary data
     *
     * @return Result[] Observed behavior assertions
     */
    public function build(): array
    {
        $results = [];
        $builder = new Builders\SessionDataBuilder();
        $row = new Row([
            'payload' => "a\0\xffb",
        ], new ParametersValidator());
        $entity = $builder->build($row);
        $results[] = (new Validator\Strings(
            $entity->getPayload()
        ))->assertEquals(
            "a\0\xffb",
            "getPayload exposes the validated projection value"
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
        $builder = new Builders\SessionDataBuilder();
        $values = [
            'payload' => "a\0\xffb",
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
     * Materializes streamed session payloads without corrupting binary bytes
     *
     * @return Result[] Observed behavior assertions
     */
    public function testStreamedPayload(): array
    {
        $results = [];
        $stream = fopen("php://memory", "w+b");
        try {
            fwrite($stream, "a\0\xffb");
            rewind($stream);
            $builder = new Builders\SessionDataBuilder();
            $entity = $builder->build(new Row(
                ["payload" => $stream],
                new ParametersValidator()
            ));
            $results[] = (new Validator\Strings(
                $entity->getPayload()
            ))->assertEquals(
                "a\0\xffb",
                "PostgreSQL-style streams retain every payload byte"
            );
        } finally {
            fclose($stream);
        }
        return $results;
    }
}
