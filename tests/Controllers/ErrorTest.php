<?php

namespace Test\Lucinda\Project\Controllers;

use Lucinda\Project\Controllers\Error;
use Lucinda\Templating\ViewException;
use Lucinda\Templating\Wrapper;
use Lucinda\UnitTest\Result;

/** Verifies original VL diagnostics and safe debug-template rendering. */
final class ErrorTest
{
    /** Checks source mapping and preservation of the underlying PHP failure. */
    public function templateDiagnostics(): array
    {
        $cause = new \Error('Undefined function <unsafe>');
        $exception = new ViewException('Rendering failed', 0, $cause, ['source' => 'templates/tags/card.html', 'line' => 12]);
        $data = $this->data($exception);
        return [
            new Result($data['file'] === 'templates/tags/card.html' && $data['line'] === 12, 'Original template location'),
            new Result($data['template'] === ['file' => 'templates/tags/card.html', 'line' => 12], 'Structured VL context'),
            new Result($data['cause']['type'] === \Error::class && $data['cause']['message'] === $cause->getMessage(), 'Underlying PHP error'),
            new Result($data['cause']['file'] === $cause->getFile() && $data['cause']['trace'] === $cause->getTraceAsString(), 'PHP location and trace')
        ];
    }

    /** Checks fallback diagnostics when source maps or previous failures are absent. */
    public function fallbackDiagnostics(): array
    {
        $exception = new ViewException('Invalid tag');
        $data = $this->data($exception);
        $ordinary = $this->data(new \RuntimeException('Ordinary failure'));
        return [
            new Result($data['file'] === $exception->getFile() && $data['line'] === $exception->getLine(), 'Fallback PHP location'),
            new Result(!isset($data['template'], $data['cause']), 'No invented VL context'),
            new Result(!isset($ordinary['template'], $ordinary['cause']) && $ordinary['message'] === 'Ordinary failure', 'Ordinary exceptions unchanged')
        ];
    }

    /** Compiles both real debug views and checks HTML escaping and optional sections. */
    public function debugViews(): array
    {
        $root = dirname(__DIR__, 2);
        $temporary = sys_get_temp_dir().'/lucinda-debug-'.bin2hex(random_bytes(8));
        mkdir($temporary, 0700);
        mkdir($temporary.'/tags');
        mkdir($temporary.'/compiled');
        $xml = new \SimpleXMLElement('<xml><templating/></xml>');
        $xml->templating['compilations_path'] = $temporary.'/compiled';
        $xml->templating['tags_path'] = $temporary.'/tags';
        $xml->templating['templates_path'] = $root.'/templates/views';
        $xml->templating['templates_extension'] = 'html';
        try {
            $wrapper = new Wrapper($xml);
            $data = $this->data(new ViewException('<script>alert(1)</script>', 0, new \Error('<unsafe>'), ['source' => 'tags/card.html', 'line' => 12]));
            $html = $wrapper->compile('debug', $data);
            $console = $wrapper->compile('debug-console', $data);
            $plain = $wrapper->compile('debug', $this->data(new \RuntimeException('Ordinary failure')));
            return [
                new Result(str_contains($html, 'ViewLanguage template') && str_contains($html, 'tags/card.html'), 'HTML template context'),
                new Result(str_contains($html, 'Underlying error') && str_contains($html, '&lt;unsafe&gt;'), 'Underlying error is escaped'),
                new Result(!str_contains($html, '<script>') && str_contains($html, '&lt;script&gt;'), 'Debug message is escaped'),
                new Result(str_contains($console, 'tags/card.html:12') && str_contains($console, 'Underlying error'), 'Console context'),
                new Result(!str_contains($plain, 'ViewLanguage template') && !str_contains($plain, 'Underlying error'), 'Optional context stays optional')
            ];
        } finally {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($temporary, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($temporary);
        }
    }

    /** Exercises the shared diagnostic mapper without constructing unrelated request services. */
    private function data(\Throwable $exception): array
    {
        $class = new \ReflectionClass(Error::class);
        return $class->getMethod('compileData')->invoke($class->newInstanceWithoutConstructor(), $exception);
    }
}
