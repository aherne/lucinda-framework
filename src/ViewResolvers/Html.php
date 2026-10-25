<?php

namespace Lucinda\Project\ViewResolvers;

use Lucinda\MVC\Response\ViewResolver;
use Lucinda\MVC\Response\View;
use Lucinda\Templating\Wrapper;

final class Html implements ViewResolver
{
    const XML_PATH = "xml/templating";

    public function resolve(View $view): string
    {
        $wrapper = new Wrapper($this->getXML());
        return $wrapper->compile($view->getFile(), $view->getData());
    }

    private function getXML(): \SimpleXMLElement
    {
        $xmlFile = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.self::XML_PATH.".xml";
        if (!file_exists($xmlFile)) {
            throw new \RuntimeException(
                "XML file not found: ".$xmlFile
            );
        }
        $xml = simplexml_load_file($xmlFile);
        if ($xml === false) {
            throw new \RuntimeException(
                "Templating XML is malformed: ".$xmlFile
            );
        }
        return $xml;
    }
}