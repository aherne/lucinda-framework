<?php

namespace Test\Lucinda\Project\Support;

/** Runs one disposable PHP process for exit paths and global error handlers. */
final class PhpProcess
{
    /** Fixture owning the child's configuration, script and output files */
    private ProjectFixture $project;

    /**
     * Captures the reversible project configuration used by the child
     *
     * @param ProjectFixture $project Test-owned configuration and output paths
     */
    public function __construct(ProjectFixture $project)
    {
        $this->project = $project;
    }

    /**
     * Writes a private script using the project's normal Composer autoloader
     *
     * @param string $code PHP statements without an opening tag
     * @return string Absolute script path inside the fixture
     */
    public function writeScript(string $code): string
    {
        $autoload = var_export(
            dirname(__DIR__, 2) . '/vendor/autoload.php',
            true
        );
        $script = $this->project->getRuntimePath() . '/process-case.php';
        file_put_contents($script, "<?php\n"
            . '$loader = require ' . $autoload . ";\n" . $code);
        return $script;
    }

    /**
     * Executes the script with bounded waiting and separate diagnostic output
     *
     * @param string $code PHP statements to execute in the child
     * @return array<string, int|string> Exit status, stdout and stderr
     * @throws \RuntimeException If startup fails or the process times out
     */
    public function run(string $code): array
    {
        $script = $this->writeScript($code);
        $output = $this->project->getRuntimePath() . '/process.stdout';
        $errors = $this->project->getRuntimePath() . '/process.stderr';
        $process = proc_open([PHP_BINARY, $script], [
            0 => ['file', '/dev/null', 'r'],
            1 => ['file', $output, 'w'],
            2 => ['file', $errors, 'w'],
        ], $pipes, $this->project->getPath());
        if (!is_resource($process)) {
            throw new \RuntimeException('Cannot start isolated PHP test');
        }
        $deadline = microtime(true) + 10;
        try {
            do {
                $status = proc_get_status($process);
                if (!$status['running']) {
                    return [
                        'status' => $status['exitcode'],
                        'stdout' => file_get_contents($output),
                        'stderr' => file_get_contents($errors),
                    ];
                }
                usleep(10000);
            } while (microtime(true) < $deadline);
            throw new \RuntimeException('Isolated PHP test timed out');
        } finally {
            if (proc_get_status($process)['running']) {
                proc_terminate($process);
            }
            proc_close($process);
        }
    }
}
