<?php

namespace Lucinda\Project\ViewResolvers;

use Lucinda\ConsoleViewLanguage\RenderMode;
use Lucinda\ConsoleViewLanguage\Wrapper as ConsoleWrapper;
use Lucinda\Framework\XmlDetector;
use Lucinda\Templating\Wrapper as TemplatingWrapper;
use Lucinda\MVC\Response\View;
use Lucinda\MVC\Response\ViewResolver;

final class Console implements ViewResolver
{
    const XML_PATH = "xml/templating-console";

    public function resolve(View $view): string
    {
        // template
        $detector = new XmlDetector(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.self::XML_PATH);
        $wrapper1 = new TemplatingWrapper($detector->getXML());
        $output = $wrapper1->compile($view->getFile(), $view->getData());

        // compile console view language
        $wrapper2 = new ConsoleWrapper($output, RenderMode::ANSI);
        return $wrapper2->getBody();
    }
}