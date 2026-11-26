<?php

namespace Test\Lucinda\Project\STDERR;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\STDERR as Errors;
use Test\Lucinda\Project\Support;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies FatalErrorResolver presentation behavior. */
final class FatalErrorResolverTest
{
    /**
     * Retains current and preceding exception diagnostics in CLI JSON output
     *
     * @return Result[] Observed behavior assertions
     */
    public function resolve(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $resolver = new Errors\FatalErrorResolver(
            $project->getPath() . '/xml/stderr/application'
        );
        $current = new \RuntimeException("failure \xff");
        $previous = new \LogicException('original');
        $data = json_decode(
            $resolver->resolve($current, $previous),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $results[] = (new Validator\Arrays(
            [$data['type']]
        ))->assertIdentical(
            [\RuntimeException::class],
            'Current exception class retained'
        );
        $results[] = (new Validator\Arrays(
            [$data['file']]
        ))->assertIdentical(
            [$current->getFile()],
            'Current exception file retained'
        );
        $results[] = (new Validator\Arrays(
            [$data['line']]
        ))->assertIdentical(
            [$current->getLine()],
            'Current exception line retained'
        );
        $results[] = (new Validator\Strings(
            $data['previous']['message']
        ))->assertEquals(
            'original',
            'Original failure retained separately'
        );
        $results[] = (new Validator\Booleans(
            str_contains($data['message'], "\xef\xbf\xbd")
        ))->assertTrue(
            'Invalid UTF-8 is replaced in diagnostics'
        );
        $data = json_decode($resolver->resolve(new \Exception('single')), true);
        $results[] = (new Validator\Booleans(
            !array_key_exists('previous', $data)
        ))->assertTrue(
            'Absent original failure adds no previous payload'
        );
        return $results;
    }

    /**
     * Renders escaped current and preceding exceptions under an HTTP SAPI
     *
     * @return Result[] Observed behavior assertions
     */
    public function testHtmlFallback(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $project->write(
            'xml/stderr/application.xml',
            '<xml><application default_format="html"/></xml>'
        );
        $server = new Support\HttpServer($project, <<<'CODE'
        $resolver = new \Lucinda\Project\STDERR\FatalErrorResolver(
            getcwd() . '/xml/stderr/application'
        );
        echo $resolver->resolve(
            new \RuntimeException('<script>unsafe</script>'),
            new \LogicException('original & failure'));
        CODE);
        try {
            $body = $server->get();
            $results[] = (new Validator\Booleans(
                str_contains(
                    $body,
                    '&lt;script&gt;unsafe&lt;/script&gt;'
                )
            ))->assertTrue(
                'HTML fallback escapes exception text'
            );
            $results[] = (new Validator\Booleans(
                !str_contains($body, '<script>unsafe</script>')
            ))->assertTrue(
                'Diagnostic text cannot inject markup'
            );
            $results[] = (new Validator\Booleans(
                str_contains($body, 'original &amp; failure')
            ))->assertTrue(
                'Previous exception is escaped and rendered'
            );
        } finally {
            $server->close();
        }
        return $results;
    }

    /**
     * Stops immediately when the required fallback configuration is absent
     *
     * @return Result[] Observed behavior assertions
     */
    public function testMissingConfiguration(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $result = (new Support\PhpProcess($project))->run(
            'new \\Lucinda\\Project\\STDERR\\FatalErrorResolver(getcwd() . '
            . '"/missing"); '
            . 'echo "unreachable";'
        );
        $results[] = (new Validator\Booleans(
            str_contains(
                $result['stdout'],
                'XML that contains default resolver is mandatory'
            )
        ))->assertTrue(
            'Missing fallback configuration produces a direct explanation'
        );
        $results[] = (new Validator\Booleans(
            !str_contains($result['stdout'], 'unreachable')
        ))->assertTrue(
            'Fallback initialization terminates'
        );
        return $results;
    }
}
