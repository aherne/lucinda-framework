<?php
use Lucinda\Framework\Json;

/**
 * STDERR MVC view resolver for JSON format.
 */
class JsonRenderer extends \Lucinda\STDERR\ViewResolver
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDERR\Runnable::run()
     */
    public function run(): void
    {
        $json = new Json();
        $this->response->setBody($json->encode(array("status"=>"error","body"=>$this->response->view()->getData())));
    }
}
