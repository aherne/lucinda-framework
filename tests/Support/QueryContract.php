<?php

namespace Test\Lucinda\Project\Support;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Framework\Persistence\Queries\Query;

/** Verifies one generated SQL operation against its reviewed query contract. */
final class QueryContract
{
    /** Operation whose SQL and typed bindings are under test */
    private Query $query;

    /** @var array<string, mixed> Reviewed SQL and binding expectations */
    private array $expected;

    /**
     * Captures an operation and its independently retained regression fixture
     *
     * @param Query $query Generated operation with hostile fixture input
     * @param string $identity Fixture key identifying operation and dialect
     */
    public function __construct(Query $query, string $identity)
    {
        $this->query = $query;
        $fixtures = json_decode(file_get_contents(
            dirname(__DIR__) . '/Fixtures/queries.json'
        ), true, 512, JSON_THROW_ON_ERROR);
        $this->expected = $fixtures[$identity];
    }

    /**
     * Checks SQL semantics, placeholder completeness and normalized bindings
     *
     * @return Result[] Contract checks for this operation
     */
    public function verify(): array
    {
        $results = [];
        $sql = (string) $this->query->getSql();
        $results[] = (new Validator\Arrays(
            [$sql]
        ))->assertIdentical(
            [$this->expected['sql']],
            'SQL retains its selected fields, predicates and atomic clauses'
        );
        $results[] = (new Validator\Arrays(
            [$this->query->isSelection()]
        ))->assertIdentical(
            [$this->expected['selection']],
            'Operation category is explicit'
        );
        $bindings = [];
        foreach ($this->query->getParameters() as $name => $binding) {
            $value = $binding['value'];
            if (is_resource($value)) {
                $position = ftell($value);
                $value = stream_get_contents($value);
                fseek($binding['value'], $position);
            }
            if (
                ($this->expected['bindings'][$name]['value'] ?? null)
                === '<current-time>'
            ) {
                $results[] = (new Validator\Booleans(
                    is_int($value) && abs(time() - $value) < 5
                ))->assertTrue(
                    'Migration timestamp is a current integer Unix time'
                );
                $value = '<current-time>';
            }
            $bindings[$name] = [
                'value' => $value,
                'type' => $binding['type']->name,
            ];
            if (is_string($value) && str_starts_with($value, "input-'")) {
                $results[] = (new Validator\Booleans(
                    !str_contains($sql, $value)
                ))->assertTrue(
                    'Hostile runtime text appears only in bound parameters'
                );
            }
        }
        $results[] = (new Validator\Arrays(
            [$bindings]
        ))->assertIdentical(
            [$this->expected['bindings']],
            'All runtime values retain their documented SQL binding types'
        );
        preg_match_all('/(?<!:):[A-Za-z_][A-Za-z0-9_]*/', $sql, $matches);
        $names = array_values(array_unique($matches[0]));
        $boundNames = array_keys($bindings);
        sort($names);
        sort($boundNames);
        $results[] = (new Validator\Arrays(
            [$boundNames]
        ))->assertIdentical(
            [$names],
            'Every SQL placeholder has exactly its intended binding'
        );
        return $results;
    }
}
