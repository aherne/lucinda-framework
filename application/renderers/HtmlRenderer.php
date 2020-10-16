<?php
/**
 * STDERR MVC error renderer for HTML format.
 */
class HtmlRenderer extends \Lucinda\MVC\STDERR\ErrorRenderer implements \Lucinda\MVC\STDERR\ErrorHandler
{
    /**
     * @var \Lucinda\MVC\STDERR\ErrorHandler
     */
    private $defaultErrorHandler;

    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDERR\ErrorRenderer::render()
     */
    public function render(Lucinda\MVC\STDERR\Response $response)
    {
        $viewFile = $response->getView();
        if ($viewFile) {
            if (!file_exists($viewFile.".html")) {
                throw new \Lucinda\MVC\STDERR\Exception("View file not found: ".$viewFile);
            }

            try {
                $this->defaultErrorHandler = \Lucinda\MVC\STDERR\PHPException::getErrorHandler();

                // take control of error handling
                \Lucinda\MVC\STDERR\PHPException::setErrorHandler($this);
                set_exception_handler(array($this, "handle"));

                // compiles PHP file into output buffer
                ob_start();
                $_VIEW = $response->attributes();
                require($viewFile . ".html");
                $output = ob_get_contents();
                ob_end_clean();

                // restores default error handler
                \Lucinda\MVC\STDERR\PHPException::setErrorHandler($this->defaultErrorHandler);
                set_exception_handler(array($this->defaultErrorHandler, "handle"));

                // saves stream
                $response->getOutputStream()->write($output);
            } catch (Exception $e) {
                $this->handle($e);
            } catch (Throwable $e) {
                $this->handle($e);
            }
        }
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
