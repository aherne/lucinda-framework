<?php

namespace Lucinda\Project\ViewResolvers;

use Lucinda\Templating\Wrapper as TemplatingWrapper;
use Lucinda\MVC\ViewResolver;
use Lucinda\STDERR\ErrorHandler;
use Lucinda\STDERR\PHPException;
use Lucinda\Console\Wrapper as ConsoleWrapper;

/**
 * MVC view resolver for HTML format using ViewLanguage templating.
 */
class Console extends ViewResolver implements ErrorHandler
{
    /**
     * @var ErrorHandler
     */
    private ErrorHandler $defaultErrorHandler;

    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        if ($this->response->getBody()) {
            $this->response->setBody("\n");
            return;
        }

        // gets view file
        try {
            $this->defaultErrorHandler = PHPException::getErrorHandler();

            // converts view language to PHP
            $wrapper = new TemplatingWrapper($this->application->getXML());

            // take control of error handling
            PHPException::setErrorHandler($this);
            set_exception_handler(array($this,"handle"));

            // compiles PHP file into output buffer
            $output = $wrapper->compile($this->response->view()->getFile(), $this->response->view()->getData());

            // processes output stream for tags
            $ctp = new ConsoleWrapper($output);
            $output = $ctp->getBody();

            // restores default error handler
            PHPException::setErrorHandler($this->defaultErrorHandler);
            set_exception_handler(array($this->defaultErrorHandler, "handle"));

            // saves stream
            $this->response->setBody($output);
        } catch (\Throwable $e) {
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
