<?php
// adds root to include path
set_include_path(get_include_path(). PATH_SEPARATOR . __DIR__);

// performs environment detection
$environment = getenv("ENVIRONMENT");
if(!$environment) die("Value of environment variable 'ENVIRONMENT' could not be detected!");

// takes control of STDERR
require_once("vendor/lucinda/errors-mvc/src/FrontController.php");
require_once("application/models/errors/EmergencyHandler.php");
new Lucinda\MVC\STDERR\FrontController("errors.xml", $environment, new EmergencyHandler());

// takes control of STDOUT
require_once("vendor/lucinda/mvc/loader.php");
new Lucinda\MVC\STDOUT\FrontController("configuration.xml");