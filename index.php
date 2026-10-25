<?php

use Lucinda\MVC\EventScheduler;
use Lucinda\MVC\EventType;
use Lucinda\Project\STDERR\DisplayErrors;
use Lucinda\Project\STDERR\ErrorReporter;
use Lucinda\Project\STDERR\FatalErrorResolver;

require "vendor/autoload.php";

// performs environment detection
$environment = getenv("ENVIRONMENT");
if (!$environment) {
    die("Value of environment variable 'ENVIRONMENT' could not be detected!");
}
define("ENVIRONMENT", $environment);

// handles STDERR flow
new Lucinda\STDERR\FrontController(
    __DIR__."/stderr.xml", 
    __DIR__,
    new ErrorReporter(
        $_SERVER,
        __DIR__."/xml/stderr/loggers",
        __DIR__."/xml/stderr/loggers-fatal"
    ),
    new FatalErrorResolver(
        __DIR__."/xml/stderr/application"
    ),
    new DisplayErrors());

// handles STDOUT flow
$eventScheduler = new EventScheduler();
if (PHP_SAPI === "cli") {
    $eventScheduler->add(
        EventType::APPLICATION,
        Lucinda\Project\EventListeners\Console\Error::class
        );
    $object = new Lucinda\ConsoleSTDOUT\FrontController(
        __DIR__."/stdout.xml",
        $eventScheduler
    );
    $object->run();
} else {
    $eventScheduler->add(
        EventType::REQUEST,
        Lucinda\Project\EventListeners\Error::class
        );
    $object = new Lucinda\STDOUT\FrontController(
        __DIR__."/stdout.xml",
        $eventScheduler
    );
    $object->run();
}
