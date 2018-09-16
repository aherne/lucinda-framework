<?php
// performs environment detection
$environment = getenv("ENVIRONMENT");
if(!$environment) die("Value of environment variable 'ENVIRONMENT' could not be detected!");

// takes control of STDERR
require_once("vendor/lucinda/errors-mvc/src/FrontController.php");
require_once("application/models/errors/EmergencyHandler.php");
require_once("application/models/errors/reporters/LogReporter.php");
new Lucinda\MVC\STDERR\FrontController("errors.xml", $environment, __DIR__, new EmergencyHandler());

// takes control of STDOUT
require_once("vendor/lucinda/mvc/loader.php");
new Lucinda\MVC\STDOUT\FrontController("configuration.xml");