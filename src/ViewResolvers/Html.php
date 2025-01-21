<?php

namespace Lucinda\Project\ViewResolvers;

use Lucinda\Project\TemplatingWrapper;
use Lucinda\MVC\ViewResolver;

/**
 * MVC view resolver for HTML format using ViewLanguage templating.
 */
class Html extends ViewResolver
{
    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        if ($this->response->getBody()) {
            return;
        }

        // converts view language to PHP
        $wrapper = new TemplatingWrapper($this->application->getTag("templating")->xpath("..")[0]);

        // compiles PHP file into output buffer
        $output = $wrapper->compile($this->response->view()->getFile(), $this->response->view()->getData());

        // saves stream
        $this->response->setBody($output);
    }
}
