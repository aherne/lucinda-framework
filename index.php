<?php
// takes charge of errors
ini_set("display_errors",1);
require_once("libraries/error-reporting/loader.php");
require_once("application/models/GeneralErrorReporting.php");
PHPException::setErrorHandler(GeneralErrorReporting::class);

// starts MVC api and loads other libraries
require_once("libraries/servlets-api/loader.php");
try {
    new FrontController();
} catch(Exception $e) {
    new GeneralErrorReporting($e);
}