<?php
use Lucinda\Templating\Wrapper;

/**
 * STDERR MVC view resolver for HTML format using ViewLanguage templating.
 */
class HtmlRenderer extends \Lucinda\STDERR\ViewResolver implements \Lucinda\STDERR\ErrorHandler
{
    /**
     * @var \Lucinda\STDERR\ErrorHandler
     */
    private $defaultErrorHandler;
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDERR\Runnable::run()
     */
    public function run(): void
    {
        if ($this->response->getBody()) {
            return;
        }
        
        // gets view file
        try {
            $this->defaultErrorHandler = \Lucinda\STDERR\PHPException::getErrorHandler();
            
            // converts view language to PHP
            $wrapper = new Wrapper($this->application->getXML());
            
            // take control of error handling
            \Lucinda\STDERR\PHPException::setErrorHandler($this);
            set_exception_handler(array($this,"handle"));
            
            // compiles PHP file into output buffer
            $output = $wrapper->compile($this->response->view()->getFile(), $this->response->view()->getData());
            
            // restores default error handler
            \Lucinda\STDERR\PHPException::setErrorHandler($this->defaultErrorHandler);
            set_exception_handler(array($this->defaultErrorHandler, "handle"));
            
            // saves stream
            $this->response->setBody($output);
        } catch (Throwable $e) {
            $this->handle($e);
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDERR\ErrorHandler::handle()
     */
    public function handle(\Throwable $exception): void
    {
        // close output buffer
        if (ob_get_length()) {
            ob_end_clean();
        }

        // delegate handling to STDERR MVC API
        $this->defaultErrorHandler->handle($exception);
    }
}
