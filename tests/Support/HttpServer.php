<?php

namespace Test\Lucinda\Project\Support;

/** Owns a temporary local PHP server for testing actual HTTP SAPI behavior. */
final class HttpServer
{
    /** Server process terminated when the fixture is closed */
    private mixed $process;

    /** Loopback endpoint reserved for this server */
    private string $address;

    /**
     * Starts a loopback server using the fixture's private router script
     *
     * @param ProjectFixture $project Reversible configuration and runtime files
     * @param string $code PHP statements to execute for each HTTP request
     * @throws \RuntimeException If the server cannot be started
     */
    public function __construct(ProjectFixture $project, string $code)
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        if ($socket === false) {
            throw new \RuntimeException('Cannot reserve loopback endpoint');
        }
        $this->address = stream_socket_get_name($socket, false);
        fclose($socket);
        $script = (new PhpProcess($project))->writeScript($code);
        $log = $project->getRuntimePath() . '/server.log';
        $this->process = proc_open([
            PHP_BINARY, '-S', $this->address, $script,
        ], [
            0 => ['file', '/dev/null', 'r'],
            1 => ['file', $log, 'a'],
            2 => ['file', $log, 'a'],
        ], $pipes, $project->getPath());
        if (!is_resource($this->process)) {
            throw new \RuntimeException('Cannot start local PHP server');
        }
        $deadline = microtime(true) + 5;
        do {
            $connection = @stream_socket_client(
                'tcp://' . $this->address,
                $errorCode,
                $errorMessage,
                0.1
            );
            if (is_resource($connection)) {
                fclose($connection);
                error_clear_last();
                return;
            }
            usleep(10000);
        } while (microtime(true) < $deadline);
        $this->close();
        throw new \RuntimeException('Local PHP server did not become ready');
    }

    /**
     * Gets a response from the private server
     *
     * @return string Complete HTTP response body
     * @throws \RuntimeException If the request fails
     */
    public function get(): string
    {
        $context = stream_context_create([
            'http' => ['timeout' => 5, 'ignore_errors' => true],
        ]);
        $body = file_get_contents(
            'http://' . $this->address . '/',
            false,
            $context
        );
        if ($body === false) {
            throw new \RuntimeException('Local test request failed');
        }
        return $body;
    }

    /**
     * Stops only this fixture's server process
     *
     * @return void
     */
    public function close(): void
    {
        if (is_resource($this->process)) {
            proc_terminate($this->process);
            proc_close($this->process);
            $this->process = null;
        }
    }
}
