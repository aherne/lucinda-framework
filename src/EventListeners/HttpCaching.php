<?php
namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\CacheableFinder;
use Lucinda\STDOUT\EventListeners\Response;
use Lucinda\Project\Attributes;

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
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $validator = $this->attributes->getHeaders();
        if ($validator===null || $this->request->getMethod()!="GET") {
            return;
        }
        $cacheableFinder = new CacheableFinder($this->application, $this->request, $this->response);
        $httpStatus = $validator->validateCache($cacheableFinder->getResult(), $this->request->getMethod());
        if (!in_array($httpStatus, [200,412])) {
            $this->response->setBody("");
        }
        $this->response->setStatus($httpStatus);
        $headers = $validator->getResponse()->toArray();
        foreach ($headers as $name=>$value) {
            $this->response->headers($name, $value);
        }
    }
}
