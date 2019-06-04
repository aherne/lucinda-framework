<?php
require_once(dirname(__DIR__)."/models/json/Json.php");

/**
 * STDERR MVC error renderer for JSON format.
 */
class JsonRenderer implements \Lucinda\MVC\STDERR\ErrorRenderer
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDERR\ErrorRenderer::render()
     */
    public function render(Lucinda\MVC\STDERR\Response $response) {
        $json = new Json();
        $response->getOutputStream()->write($json->encode(array("status"=>"error","body"=>$response->attributes())));
    }
}