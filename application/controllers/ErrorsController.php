<?php
/**
 * STDERR MVC controller that gets activated whenever an non-routed error occurs during application lifecycle.
 */
class ErrorsController extends Lucinda\STDERR\Controller
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
     *
     * @throws Exception If content type of response is other than JSON or HTML.
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
            $view->setFile($this->application->getViewsPath()."/".($displayErrors?"debug":"500"));
        }
    }
}
