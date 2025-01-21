<?php

namespace Lucinda\Project\Controllers;

use Lucinda\MVC\Response\HttpStatus;
use Lucinda\MVC\Response\View;
use Lucinda\Project\ViewCompilationException;
use Lucinda\STDERR\Controller;

/**
 * STDERR MVC controller that gets activated whenever an non-routed error occurs during application lifecycle.
 */
class Error extends Controller
{
    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $this->setResponseStatus();
        $this->setResponseBody();
    }

    /**
     * Sets response status to HTTP status code 500
     */
    private function setResponseStatus(): void
    {
        $this->response->setStatus(HttpStatus::INTERNAL_SERVER_ERROR);
    }

    /**
     * Sets response body from view file or stream.
     */
    private function setResponseBody(): void
    {
        $contentType = $this->response->headers("Content-Type");
        $exception = $this->request->getException();
        $view = $this->response->view();
        $displayErrors = $this->application->getDisplayErrors();
        if (str_starts_with($contentType, "text/html")) {
            $this->handleHTML($exception, $view, $displayErrors);
        } elseif (str_starts_with($contentType, "application/json")) {
            $this->handleJSON($exception, $view, $displayErrors);
        } elseif (str_starts_with($contentType, "text/plain")) {
            $this->handleConsole($exception, $view, $displayErrors);
        } else {
            throw new \Exception("Unsupported content type!");
        }
    }

    /**
     * Handles HTML error response
     *
     * @param \Throwable $exception
     * @param View $view
     * @param bool $displayErrors
     * @return void
     */
    private function handleHTML(\Throwable $exception, View $view, bool $displayErrors): void
    {
        if ($displayErrors) {
            $view["message"] = $exception->getMessage();
            if ($exception instanceof \Lucinda\SQL\StatementException) {
                $view["query"] = $exception->getQuery();
            }
            $view["type"] = get_class($exception);
            if ($exception instanceof ViewCompilationException) {
                $view["file"] = $exception->getPrevious()->getFile();
                $view["line"] = $exception->getPrevious()->getLine();
                $view["trace"] = implode("\n", $exception->getTemplateTrace());
            } else {
                $view["file"] = $exception->getFile();
                $view["line"] = $exception->getLine();
                $view["trace"] = $exception->getTraceAsString();
            }

            $view->setFile($this->application->getViewsPath()."/debug");
        } else {
            $view->setFile($this->application->getViewsPath()."/500");
        }
    }

    /**
     * Handles JSON error response
     *
     * @param \Throwable $exception
     * @param View $view
     * @param bool $displayErrors
     * @return void
     */
    private function handleJSON(\Throwable $exception, View $view, bool $displayErrors): void
    {
        if ($displayErrors) {
            $view["message"] = $this->request->getException()->getMessage();
            $view["file"] = $this->request->getException()->getFile();
            $view["line"] = $this->request->getException()->getLine();
            if ($exception instanceof \Lucinda\SQL\StatementException) {
                $view["query"] = $this->request->getException()->getQuery();
            }
        } else {
            $view["message"] = "Internal Server Error";
        }
    }

    /**
     * Handles console error response
     *
     * @param \Throwable $exception
     * @param View $view
     * @param bool $displayErrors
     * @return void
     * @throws \Lucinda\MVC\ConfigurationException
     */
    private function handleConsole(\Throwable $exception, View $view, bool $displayErrors): void
    {
        if ($displayErrors) {
            $view["message"] = $exception->getMessage();
            if ($exception instanceof \Lucinda\SQL\StatementException) {
                $view["query"] = $exception->getQuery();
            }
            $view["type"] = get_class($exception);
            $view["file"] = $exception->getFile();
            $view["line"] = $exception->getLine();
            $view["trace"] = $exception->getTraceAsString();
            $viewsPath = (string) $this->application->getTag("templating")["templates_path"];
            $view->setFile($viewsPath."/debug-console");
        } else {
            $this->response->setBody("Internal Server Error");
        }
    }
}
