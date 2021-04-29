<?php
use Lucinda\MVC\Response;

/**
 * Performs CORS validation of HTTP request headers for OPTIONS request and sends response accordingly
 */
class HttpCorsListener extends \Lucinda\STDOUT\EventListeners\Request
{
    /**
     * @var Attributes
     */
    protected $attributes;
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $validator = $this->attributes->getHeaders();
        if ($validator===null || $this->request->getMethod()!="OPTIONS") {
            return;
        }
        
        // perform CORS validation
        $validator->validateCors($this->request->getServer()->getName());
        
        // send response immediately
        $response = new Response("application/json", "");
        $headers = $validator->getResponse()->toArray();
        foreach ($headers as $name=>$value) {
            $response->headers($name, $value);
        }
        $response->commit();
        exit();
    }
}
