<?php
require("vendor/autoload.php");

// performs environment detection
$environment = getenv("ENVIRONMENT");
if (!$environment) {
    die("Value of environment variable 'ENVIRONMENT' could not be detected!");
}
define("ENVIRONMENT", $environment);

// handles STDERR flow
require("vendor/lucinda/errors-mvc/loader.php"); // preloads classes always required in handling
require("application/models/EmergencyHandler.php");
new Lucinda\STDERR\FrontController("stderr.xml", ENVIRONMENT, __DIR__, new EmergencyHandler());

// handles STDOUT flow
require("vendor/lucinda/mvc/loader.php"); // preloads classes always required in handling
require("application/models/Attributes.php");
$object = new Lucinda\STDOUT\FrontController("stdout.xml", new Attributes(__DIR__."/application/listeners"));
$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "ErrorListener");
$object->run();
