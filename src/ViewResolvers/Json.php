<?php
namespace Lucinda\Project\ViewResolvers;

use Lucinda\MVC\ViewResolver;

/**
 * MVC view resolver for JSON format.
 */
class Json extends ViewResolver
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        // see who triggered resolver (tested to have zero performance impact)
        $isError = (strpos(str_replace("\\", "/", debug_backtrace()[0]["file"]), "vendor/lucinda/errors-mvc/")!==false);
        
        // resolves response in json format
        $json = new \Lucinda\Framework\Json();
        $this->response->setBody($json->encode(array("status"=>(!$isError?"ok":"error"),"body"=>$this->response->view()->getData())));
    }
}
