<?php
require_once(dirname(__DIR__, 2)."/json/Json.php");

/**
 * STDERR MVC error renderer for JSON format.
 */
class JsonRenderer implements \Lucinda\MVC\STDERR\ErrorRenderer
{
    public function render(Lucinda\MVC\STDERR\Response $response) {
        $json = new Json();
        $response->setBody($json->encode($response->getAttributes()));
    }
}