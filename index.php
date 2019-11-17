<?php
// performs environment detection
$environment = getenv("ENVIRONMENT");
if (!$environment) {
    die("Value of environment variable 'ENVIRONMENT' could not be detected!");
}
define("ENVIRONMENT", $environment);

// takes control of STDERR
require("vendor/lucinda/errors-mvc/src/FrontController.php");
require("application/models/EmergencyHandler.php");
new Lucinda\MVC\STDERR\FrontController("stderr.xml", ENVIRONMENT, __DIR__, new EmergencyHandler());

// takes control of STDOUT
require("vendor/lucinda/mvc/loader.php");
new Lucinda\MVC\STDOUT\FrontController("stdout.xml");