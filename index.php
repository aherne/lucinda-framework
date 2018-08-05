<?php
// perform environment detection
// TODO: clarity environment detection
// TODO: clarify content type & charset @ error renderers
// TODO: put log reporter in application/models

// take control of STDERR
require_once("vendor/lucinda/errors-mvc/src/FrontController.php");
new Lucinda\MVC\STDERR\FrontController("errors.xml", getenv("ENVIRONMENT"));

// take control of STDOUT
require_once("vendor/lucinda/mvc/loader.php");
new FrontController("configuration.xml");
