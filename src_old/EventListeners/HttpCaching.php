<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\CacheableFinder;
use Lucinda\MVC\ConfigurationException;
use Lucinda\MVC\Response\HttpStatus;
use Lucinda\STDOUT\EventListeners\Response;
use Lucinda\Project\Attributes;
use Lucinda\STDOUT\Request\Method;

/**
 * Sets up HTTP cache headers validation and updates response headers accordingly
 */
class HttpCaching extends Response
{
    /**
     * @var Attributes
     */
    protected \Lucinda\STDOUT\Attributes $attributes;

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
        $httpStatus = $validator->validateCache($cacheableFinder->getResult(), $this->request->getMethod()->value);

        if (!in_array($httpStatus, [200,412])) {
            $this->response->setBody("");
        }

        $cases = HttpStatus::cases();
        foreach ($cases as $case) {
            if (str_starts_with($case->value, (string) $httpStatus)) {
                $this->response->setStatus($case);
                break;
            }
        }

        $headers = $validator->getResponse()->toArray();
        foreach ($headers as $name => $value) {
            $this->response->headers($name, $value);
        }
    }
}
