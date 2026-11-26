<?php

namespace Test\Lucinda\Project\STDERR;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\STDERR as Errors;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies DisplayErrors presentation behavior. */
final class DisplayErrorsTest
{
    /**
     * Enables detailed diagnostics under the default development policy
     *
     * @return Result[] Observed behavior assertions
     */
    public function shouldDisplayErrors(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $results[] = (new Validator\Booleans(
            (new Errors\DisplayErrors())->shouldDisplayErrors() === true
        ))->assertTrue(
            'Default development policy enables diagnostics'
        );
        return $results;
    }
}
