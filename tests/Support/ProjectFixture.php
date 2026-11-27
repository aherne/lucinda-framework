<?php

namespace Test\Lucinda\Project\Support;

/**
 * Owns temporary rendering and listener files used by one test method
 *
 * Adapter paths remain the application's normal paths. Every replaced file is
 * saved before its first write and restored when the fixture leaves scope.
 * Compiled views and process output belong to a private temporary directory.
 * Source files and test entry points remain untouched. Configuration files are
 * restored after each case; database fixtures read the original unit-tests.xml.
 */
final class ProjectFixture
{
    /** Project root containing the adapters' fixed configuration paths */
    private string $path;

    /** Private directory for compilations, child scripts and output */
    private string $runtimePath;

    /**
     * @var array<string, string|null> Original contents, null when absent
     */
    private array $originals = [];

    /** @var string[] Directories created only for this fixture */
    private array $directories = [];

    /** Whether temporary files have already been restored */
    private bool $closed = false;

    /**
     * Captures file state and installs reversible test configurations
     *
     * @param string $path Project root whose adapters are under test
     */
    public function __construct(string $path)
    {
        $this->path = $path;
        $this->runtimePath = sys_get_temp_dir() . '/lucinda-test-'
            . bin2hex(random_bytes(8));
        mkdir($this->runtimePath, 0700, true);
        foreach (['normal.log', 'fatal.log', 'request-log.log'] as $name) {
            $this->remember($this->path . '/' . $name);
        }
        $this->reset();
    }

    /**
     * Gets the root used by application-relative configuration lookups
     *
     * @return string Project root
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Gets the private directory owned by this method's fixture
     *
     * @return string Temporary runtime path
     */
    public function getRuntimePath(): string
    {
        return $this->runtimePath;
    }

    /**
     * Loads a fresh configuration document from the test XML fixtures
     *
     * Changes to the returned document belong only to the calling test case.
     *
     * @param string $name Path relative to tests/xml
     * @return \SimpleXMLElement Independent document for this test case
     * @throws \RuntimeException If the fixture cannot be loaded
     */
    public function getXml(string $name): \SimpleXMLElement
    {
        $xml = simplexml_load_file(dirname(__DIR__) . '/xml/' . $name);
        if ($xml === false) {
            throw new \RuntimeException('Cannot load XML fixture: ' . $name);
        }
        return $xml;
    }

    /**
     * Installs a stored XML fixture at an adapter's configuration path
     *
     * The original application file is restored with this fixture's files.
     *
     * @param string $relative Project-relative configuration file to replace
     * @param string $name Fixture path relative to tests/xml
     * @return void
     * @throws \RuntimeException If the fixture cannot be loaded or installed
     */
    public function installXml(string $relative, string $name): void
    {
        $this->write($relative, $this->getXml($name)->asXML());
    }

    /**
     * Saves a file before its first fixture-owned modification
     *
     * @param string $file Absolute path to preserve
     * @return void
     */
    private function remember(string $file): void
    {
        if (!array_key_exists($file, $this->originals)) {
            $this->originals[$file] = is_file($file)
                ? file_get_contents($file) : null;
        }
    }

    /**
     * Writes a fixture file while preserving any existing application contents
     *
     * @param string $relative Project-relative file path
     * @param string $contents Complete temporary contents
     * @return void
     */
    public function write(string $relative, string $contents): void
    {
        if (str_contains($relative, '..') || str_starts_with($relative, '/')) {
            throw new \InvalidArgumentException('Invalid fixture path');
        }
        $file = $this->path . '/' . $relative;
        $this->remember($file);
        $missing = [];
        $directory = dirname($file);
        while (!is_dir($directory)) {
            $missing[] = $directory;
            $directory = dirname($directory);
        }
        foreach (array_reverse($missing) as $directory) {
            mkdir($directory, 0700);
            $this->directories[] = $directory;
        }
        if (file_put_contents($file, $contents) === false) {
            throw new \RuntimeException('Cannot write fixture: ' . $relative);
        }
    }

    /**
     * Sets deterministic file fixtures without supplying fake storage
     *
     * @return void
     */
    public function reset(): void
    {
        $this->installXml('stdout.xml', 'project/stdout.xml');
        $this->installXml(
            'xml/stdout/application.xml',
            'project/stdout-application.xml'
        );
        $this->installXml('xml/stdout/routes.xml', 'project/routes.xml');
        $this->installXml('stderr.xml', 'project/stderr.xml');
        $this->installXml(
            'xml/stderr/application.xml',
            'project/stderr-application.xml'
        );
        foreach (
            ['xml/stderr/loggers.xml', 'xml/stderr/loggers-fatal.xml',
            'xml/stdout/loggers.xml'] as $file
        ) {
            $this->installXml($file, 'logging/empty.xml');
        }
        $this->installXml('xml/stdout/headers.xml', 'headers/cacheable.xml');
        $this->installXml('xml/stdout/sql.xml', 'empty.xml');
        $redis = $this->getXml('nosql/redis.xml');
        $redis->nosql->server['host'] =
            (getenv('PERSISTENCE_TEST_REDIS_HOST') ?: '127.0.0.1') . ':'
            . (getenv('PERSISTENCE_TEST_REDIS_PORT') ?: '6379');
        $this->write('xml/stdout/nosql.xml', $redis->asXML());
        foreach (['templating', 'templating-console'] as $name) {
            $xml = $this->getXml('project/templating.xml');
            $compilations = $this->runtimePath . '/' . $name;
            mkdir($compilations, 0700, true);
            $xml->templating['compilations_path'] = $compilations;
            $xml->templating['tags_path'] = $this->path . '/templates/tags';
            $xml->templating['templates_path'] =
                $this->path . '/templates/views';
            $this->write('xml/' . $name . '.xml', $xml->asXML());
        }
    }

    /**
     * Restores saved files and removes only fixture-owned temporary paths
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->closed) {
            return;
        }
        foreach (array_reverse($this->originals, true) as $file => $contents) {
            if ($contents === null) {
                if (is_file($file)) {
                    unlink($file);
                }
            } else {
                file_put_contents($file, $contents);
            }
        }
        foreach (array_reverse($this->directories) as $directory) {
            if (is_dir($directory) && count(scandir($directory)) === 2) {
                rmdir($directory);
            }
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->runtimePath,
                \FilesystemIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($this->runtimePath);
        $this->closed = true;
    }

    /** Restores the method's file fixtures when it leaves scope. */
    public function __destruct()
    {
        $this->close();
    }
}
