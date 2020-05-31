<?php
require("vendor/autoload.php");

// performs environment detection
$environment = getenv("ENVIRONMENT");
if (!$environment) {
    die("Value of environment variable 'ENVIRONMENT' could not be detected!");
}
define("ENVIRONMENT", $environment);

// takes control of STDERR
require("application/models/EmergencyHandler.php");
new Lucinda\STDERR\FrontController("stderr.xml", ENVIRONMENT, __DIR__, new EmergencyHandler());

// takes control of STDOUT
require("application/models/Attributes.php");
$object = new Lucinda\STDOUT\FrontController("stdout.xml", new Attributes(__DIR__."/application/listeners"));
$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "ErrorListener");
$object->run();