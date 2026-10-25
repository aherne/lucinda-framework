<?php

namespace Lucinda\Project\ViewResolvers;

use Lucinda\ConsoleViewLanguage\RenderMode;
use Lucinda\ConsoleViewLanguage\Wrapper;
use Lucinda\ConsoleViewLanguage\Exception;
use Lucinda\MVC\Response\View;
use Lucinda\MVC\Response\ViewResolver;

final class Console implements ViewResolver
{
    public function resolve(View $view): string
    {
        $wrapper = new Wrapper($this->getMarkup($view), RenderMode::ANSI);
        return $wrapper->getBody();
    }

    private function getMarkup(View $view): string
    {
        $file = $view->getFile();

        if ($file === "" || !is_file($file) || !is_readable($file)) {
            throw new Exception(
                "Console template is missing or unreadable: ".$file
            );
        }

        $data = $view->getData();

        ob_start();

        try {
            require $file;
            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }
}