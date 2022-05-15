<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\MVC\Response;
use Lucinda\Project\Attributes;
use Lucinda\STDOUT\EventListeners\Request;
use Lucinda\STDOUT\Request\Method;

/**
 * Performs CORS validation of HTTP request headers for OPTIONS request and sends response accordingly
 */
class HttpCors extends Request
{
    /**
     * @var Attributes
     */
    protected \Lucinda\STDOUT\Attributes $attributes;

    /**
     * {@inheritDoc}
     * @throws \Lucinda\Headers\UserException
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $validator = $this->attributes->getHeaders();
        if ($validator===null || $this->request->getMethod()!=Method::OPTIONS) {
            return;
        }

        // perform CORS validation
        $validator->validateCors($this->request->getProtocol()->value."://".$this->request->getServer()->getName());

        // send response immediately
        $response = new Response("application/json", "");
        $headers = $validator->getResponse()->toArray();
        foreach ($headers as $name => $value) {
            $response->headers($name, $value);
        }
        $response->commit();
        exit();
    }
}
