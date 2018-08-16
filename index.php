<?php
// adds root to include path
set_include_path(get_include_path(). PATH_SEPARATOR . __DIR__);

// performs environment detection
$environment = getenv("ENVIRONMENT");
if(!$environment) die("Value of environment variable 'ENVIRONMENT' could not be detected!");

// takes control of STDERR
require_once("vendor/lucinda/errors-mvc/src/FrontController.php");
new Lucinda\MVC\STDERR\FrontController("errors.xml", getenv("ENVIRONMENT"));

// takes control of STDOUT
require_once("vendor/lucinda/mvc/loader.php");
new FrontController("configuration.xml");
