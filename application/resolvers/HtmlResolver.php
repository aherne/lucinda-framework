<?php
/**
 * View resolver for HTML response format to be used whenever developers do not desire HTML templating (NOT RECOMMENDED!)
 */
class HtmlResolver extends \Lucinda\MVC\STDOUT\ViewResolver implements \Lucinda\MVC\STDERR\ErrorHandler
{
    /**
     * @var \Lucinda\MVC\STDERR\ErrorHandler
     */
    private $defaultErrorHandler;

    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDOUT\ViewResolver::getContent()
     */
    public function getContent()
    {
        $output = "";
        $view = $this->application->getViewsPath()."/".$this->response->getView();
        if ($view) {
            $_VIEW = $this->response->attributes();
            $view .= ".html";
            if (!file_exists($view)) {
                throw new Lucinda\MVC\STDOUT\ServletException("View file not found: ".$view);
            }

            $this->defaultErrorHandler = \Lucinda\MVC\STDERR\PHPException::getErrorHandler();

            // take control of error reporting
            \Lucinda\MVC\STDERR\PHPException::setErrorHandler($this);
            set_exception_handler(array($this,"handle"));

            // compiles PHP file into output buffer
            ob_start();
            require($view);
            $output = ob_get_contents();
            ob_end_clean();

            // restore default error handler
            \Lucinda\MVC\STDERR\PHPException::setErrorHandler($this->defaultErrorHandler);
            set_exception_handler(array($this->defaultErrorHandler,"handle"));
        }
        return $output;
    }

    /**
     * Handles errors by delegating to STDOUT MVC API
     *
     * @param \Exception $exception Encapsulates error information.
     */
    public function handle($exception)
    {
        // close output buffer
        ob_end_clean();

        // delegate handling to STDERR MVC API
        $this->defaultErrorHandler->handle($exception);
    }
}
