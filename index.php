<?php
// take control of STDERR
require_once("src/ErrorsFrontController.php");
new ErrorsFrontController();

// take control of STDOUT
require_once("vendor/lucinda/mvc/loader.php");
new FrontController();
