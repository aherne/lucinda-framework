<?php

namespace Test\Lucinda\Project\Support;

use Lucinda\STDOUT\Request;

/** Builds real immutable HTTP request snapshots without leaking globals. */
final class HttpRequest
{
    /**
     * Creates a request from controlled server values
     *
     * @param string $method HTTP request method
     * @param array<string, string> $headers Canonical request headers
     * @param string $host Receiving server name
     * @param string $uri Requested application-relative URI
     * @return Request Immutable request with its own captured values
     */
    public function create(
        string $method = 'GET',
        array $headers = [],
        string $host = 'example.test',
        string $uri = '/catalog'
    ): Request {
        $server = $_SERVER;
        $get = $_GET;
        $post = $_POST;
        $files = $_FILES;
        try {
            $_SERVER = [
                'REQUEST_METHOD' => $method,
                'REQUEST_URI' => $uri,
                'DOCUMENT_ROOT' => '/test-project',
                'SCRIPT_FILENAME' => '/test-project/index.php',
                'QUERY_STRING' => '',
                'SCRIPT_NAME' => '/index.php',
                'PHP_SELF' => '/index.php',
                'SERVER_NAME' => $host,
                'SERVER_ADDR' => '127.0.0.1',
                'SERVER_ADMIN' => 'test@example.test',
                'SERVER_SOFTWARE' => 'Test server',
                'HTTP_HOST' => $host,
                'SERVER_PORT' => '80',
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'REMOTE_ADDR' => '127.0.0.1',
                'REMOTE_PORT' => '12345',
            ];
            foreach ($headers as $name => $value) {
                $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $name))]
                    = $value;
            }
            $_GET = [];
            $_POST = [];
            $_FILES = [];
            return new Request();
        } finally {
            $_SERVER = $server;
            $_GET = $get;
            $_POST = $post;
            $_FILES = $files;
        }
    }
}
