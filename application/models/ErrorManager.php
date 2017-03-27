<?php
require_once("libraries/php-errors-api/loader.php");
require_once("libraries/php-logging-api/src/FileLogger.php");
require_once("application/models/HtmlRenderer.php");

$errorHandler = new ErrorHandler();
$errorHandler->addReporter(new FileLogger("errors"));
$errorHandler->setRenderer(new HtmlRenderer());
PHPException::setErrorHandler($errorHandler);
set_exception_handler(array($errorHandler,"handle"));