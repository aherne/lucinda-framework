<?php
class JsonRenderer implements \Lucinda\MVC\STDERR\ErrorRenderer
{
    public function render(Lucinda\MVC\STDERR\Response $response) {
        if(!headers_sent()) {
            header("HTTP/1.1 ".$response->getHttpStatus());
        }
        
        // loads headers
        $headers = $response->getHeaders();
        foreach($headers as $name=>$value) {
            header($name.": ".$value);
        }
        
        // show output
        echo json_encode($response->getBody());
    }
}