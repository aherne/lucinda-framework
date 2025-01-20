<?php

namespace Lucinda\Project;

use Lucinda\STDERR\ErrorHandler;
use Lucinda\STDERR\PHPException;
use Lucinda\Templating\Wrapper;

class TemplatingWrapper extends Wrapper  implements ErrorHandler
{
    /**
     * @var ErrorHandler
     */
    private ErrorHandler $defaultErrorHandler;

    /**
     * @param string $compilationFile
     * @param array $data
     * @return string
     */
    protected function bind(string $compilationFile, array $data): string
    {
        // take control of error handling
        $this->defaultErrorHandler = PHPException::getErrorHandler();
        PHPException::setErrorHandler($this);
        set_exception_handler(array($this,"handle"));

        // compiles PHP file into output buffer
        try {
            ob_start();
            include $compilationFile;
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        // restores default error handler
        PHPException::setErrorHandler($this->defaultErrorHandler);
        set_exception_handler(array($this->defaultErrorHandler, "handle"));

        // removes comments, by default
        return preg_replace("/<!-- VL:(START|END):\s*(.*?)\s*-->/", "", $output);
    }

    /**
     * @param \Throwable $exception
     * @return void
     */
    public function handle(\Throwable $exception): void
    {
        // close output buffer
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $viewException = new ViewCompilationException($exception->getMessage(),0, $exception);
        $viewException->setTemplateTrace($exception->getFile(), $exception->getLine());
        $this->defaultErrorHandler->handle($viewException);
    }
}