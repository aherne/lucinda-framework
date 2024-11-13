<?php
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/framework-engine/src/view_language/ViewLanguageBinder.php");

/**
 * STDERR MVC error renderer for HTML format using ViewLanguage templating.
 */
class ViewLanguageRenderer extends \Lucinda\MVC\STDERR\ErrorRenderer implements \Lucinda\MVC\STDERR\ErrorHandler
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
        if ($response->getOutputStream()->isEmpty()) {

            // gets simplexml application object
            $application = $this->application->getTag("application");

            // gets view file
            $viewFile = $response->getView();
            $viewsPath = (string) $application->paths->views;
            $viewFile = substr($viewFile, strpos($viewFile, $viewsPath)+strlen($viewsPath)+1);

            try {
                $this->defaultErrorHandler = \Lucinda\MVC\STDERR\PHPException::getErrorHandler();

                // converts view language to PHP
                $wrapper = new Lucinda\Framework\ViewLanguageBinder($application, $viewFile);
                $compilationFile = $wrapper->getCompilationFile();

                // take control of error handling
                \Lucinda\MVC\STDERR\PHPException::setErrorHandler($this);
                set_exception_handler(array($this,"handle"));

                // compiles PHP file into output buffer
                $data = $response->attributes();
                ob_start();
                require_once($compilationFile);
                $output = ob_get_contents();
                ob_end_clean();

                // restores default error handler
                \Lucinda\MVC\STDERR\PHPException::setErrorHandler($this->defaultErrorHandler);
                set_exception_handler(array($this->defaultErrorHandler,"handle"));

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
     * Handles errors by delegating to default handler
     *
     * @param \Exception $exception Encapsulates error information.
     */
    public function handle($exception)
    {
        // close output buffer
        if (ob_get_level() > 0) {
             ob_end_clean();
        }

        // delegate handling to STDERR MVC API
        $this->defaultErrorHandler->handle($exception);
    }
}
