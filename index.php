<?php
require("vendor/autoload.php");

// performs environment detection
$environment = getenv("ENVIRONMENT");
if (!$environment) {
    die("Value of environment variable 'ENVIRONMENT' could not be detected!");
}
define("ENVIRONMENT", $environment);

// handles STDERR flow
new Lucinda\STDERR\FrontController("stderr.xml", ENVIRONMENT, __DIR__, new Lucinda\Project\EmergencyHandler());

// handles STDOUT flow
if (empty($argv)) {
    $object = new Lucinda\STDOUT\FrontController("stdout.xml", new Lucinda\Project\Attributes());
    $object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, Lucinda\Project\EventListeners\Error::class);
    $object->run();
} else {
    $object = new Lucinda\ConsoleSTDOUT\FrontController("stdout.xml", new Lucinda\ConsoleSTDOUT\Attributes());
    $object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, Lucinda\Project\EventListeners\Error::class);
    $object->run();
}
