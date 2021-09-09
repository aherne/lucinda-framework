<?php
namespace Lucinda\Project\Controllers;

use Lucinda\STDERR\Controller;

/**
 * STDERR MVC controller that gets activated whenever an non-routed error occurs during application lifecycle.
 */
class Error extends Controller
{
    /**
     * {@inheritDoc}
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
        $this->response->setStatus(500);
    }

    /**
     * Sets response body from view file or stream.
     */
    private function setResponseBody(): void
    {
        // gets whether or not errors should be displayed
        $displayErrors = $this->application->getDisplayErrors();

        // gets content type
        $contentType = $this->response->headers("Content-Type");

        // sets view
        $view = $this->response->view();
        $exception = $this->request->getException();
        if ($displayErrors) {
            $view["class"] = get_class($exception);
            $view["message"] = $exception->getMessage();
            $view["file"] = $exception->getFile();
            $view["line"] = $exception->getLine();
            $view["trace"] = $exception->getTraceAsString();
        }
        if (strpos($contentType, "text/html")===0) {
            $viewsPath = (string) $this->application->getTag("templating")["templates_path"];
            $view->setFile($viewsPath."/".($displayErrors ? "debug" : "500"));
        } else if (strpos($contentType, "text/plain")===0) {
            $viewsPath = (string) $this->application->getTag("templating")["templates_path"];
            $view->setFile($viewsPath."/debug-console");
        }
    }
}
