<?php
use Lucinda\Framework\Json;

/**
 * STDOUT MVC view resolver for JSON format.
 */
class JsonResolver extends \Lucinda\STDOUT\ViewResolver
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDOUT\Runnable::run()
     */
    public function run(): void
    {
        $json = new Json();
        $this->response->setBody($json->encode(array("status"=>"ok","body"=>$this->response->view()->getData())));
    }
}
