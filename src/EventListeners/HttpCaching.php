<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\CacheableFinder;
use Lucinda\MVC\ConfigurationException;
use Lucinda\MVC\Response\HttpStatus;
use Lucinda\STDOUT\EventListeners\Response;
use Lucinda\Project\Attributes;
use Lucinda\URL\Request\Method;

/**
 * Sets up HTTP cache headers validation and updates response headers accordingly
 */
class HttpCaching extends Response
{
    /**
     * @var Attributes
     */
    protected $attributes;

    /**
     * {@inheritDoc}
     *
     * @throws ConfigurationException
     * @see    \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $validator = $this->attributes->getHeaders();
        if ($validator===null || $this->request->getMethod()!=Method::GET) {
            return;
        }

        $cacheableFinder = new CacheableFinder($this->application, $this->request, $this->response);
        $httpStatus = $validator->validateCache($cacheableFinder->getResult(), $this->request->getMethod());
        $completeHttpStatus = $this->getCompleteStatus($httpStatus);

        if (!in_array($completeHttpStatus, [HttpStatus::OK, HttpStatus::PRECONDITION_FAILED])) {
            $this->response->setBody("");
        }

        $this->response->setStatus($completeHttpStatus);

        $headers = $validator->getResponse()->toArray();
        foreach ($headers as $name => $value) {
            $this->response->headers($name, $value);
        }
    }

    /**
     * Detects complete HTTP status based on status code
     *
     * @param int $httpStatus
     * @return HttpStatus
     */
    private function getCompleteStatus(int $httpStatus): string
    {
        $reflectionClass = new \ReflectionClass(HttpStatus::class);
        $constants = $reflectionClass->getConstants();
        foreach ($constants as $constant) {
            if (strpos($constant, $httpStatus." ")===0) {
                return $constant;
            }
        }
    }
}
