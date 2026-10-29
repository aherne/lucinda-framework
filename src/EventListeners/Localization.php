<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Internationalization\Wrapper;
use Lucinda\Internationalization\Reader;
use Lucinda\Framework\ServiceRegistry;
use Lucinda\Framework\XmlDetector;
use Lucinda\MVC\EventListener\UnFaceted;
use Lucinda\STDOUT\Request;

require_once dirname(__DIR__, 2)."/helpers/translate.php";

/**
 * Sets up Internationalization API to use in automatic translation of response
 */
class Localization implements UnFaceted
{
    const XML_PATH = "xml/stdout/internationalization";
    
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function run(): void
    {
        $detector = new XmlDetector(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.self::XML_PATH);
        $wrapper = new Wrapper(
            $detector->getXML(),
            $this->request->parameters(),
            $this->request->headers()
        );
        ServiceRegistry::set(Reader::class, $wrapper->getReader());
    }
}
