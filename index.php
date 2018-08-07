<?php
// perform environment detection
$environment = getenv("ENVIRONMENT");
if(!$environment) die("Value of environment variable 'ENVIRONMENT' could not be detected!");
// TODO: clarify content type & charset @ error renderers

// take control of STDERR
require_once("vendor/lucinda/errors-mvc/src/FrontController.php");
new Lucinda\MVC\STDERR\FrontController("errors.xml", getenv("ENVIRONMENT"));

// take control of STDOUT
require_once("vendor/lucinda/mvc/loader.php");
new FrontController("configuration.xml");
