<?php
// setup conservative reporting
require_once("libraries/php-errors-api/loader.php");
require_once("src/loggers/FileLogger.php");
require_once("src/renderers/ViewRenderer.php");
$errorHandler = new ErrorHandler();
$errorHandler->addReporter(new FileLogger("errors"));
$errorHandler->setRenderer(new ViewRenderer(true));
PHPException::setErrorHandler($errorHandler);
set_exception_handler(array($errorHandler,"handle"));

// starts MVC api
require_once("libraries/php-servlets-api/loader.php");
new FrontController();