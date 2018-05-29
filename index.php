<?php
// take control of STDERR
require_once("src/error_handling/ErrorsFrontController.php");
new ErrorsFrontController();

// take control of STDOUT
require_once("vendor/lucinda/mvc/loader.php");
new FrontController();
