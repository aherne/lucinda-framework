<?php

namespace Lucinda\Project\ViewResolvers;

use Lucinda\Framework\XmlDetector;
use Lucinda\MVC\Response\ViewResolver;
use Lucinda\MVC\Response\View;
use Lucinda\Templating\Wrapper;

final class Html implements ViewResolver
{
    const XML_PATH = "xml/templating";

    public function resolve(View $view): string
    {
        $detector = new XmlDetector(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.self::XML_PATH);
        $wrapper = new Wrapper($detector->getXML());
        return $wrapper->compile($view->getFile(), $view->getData());
    }
}