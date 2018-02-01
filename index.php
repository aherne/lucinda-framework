<?php
// starts MVC api
require_once("vendor/lucinda/mvc/loader.php");
try {
	new FrontController();
} catch(PathNotFoundException $exception) {
	// below applies only if display format is HTML
	header("HTTP/1.0 404 Not Found");
	header('Content-Type: text/html; charset=UTF-8');
	require_once("application/views/404.php");
}
