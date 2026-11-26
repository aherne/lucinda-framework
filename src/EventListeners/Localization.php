<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Internationalization\Wrapper;
use Lucinda\Internationalization\Reader;
use Lucinda\Framework\ServiceRegistry;
use Lucinda\Framework\XmlDetector;
use Lucinda\MVC\EventListener\UnFaceted;
use Lucinda\STDOUT\Request;

require_once dirname(__DIR__, 2) . "/helpers/translate.php";

/**
 * Selects the request locale and registers its translation reader
 *
 * Each run loads the project locale policy and negotiates parameters and
 * headers. The translate() helper reuses the registered reader until another
 * localization run replaces it or the process ends.
 */
final class Localization implements UnFaceted
{
    /** Project-relative configuration path without its XML extension */
    public const XML_PATH = "xml/stdout/internationalization";

    /**
     * Request retained for listener execution and metadata extraction
     */
    private Request $request;

    /**
     * Captures request parameters and headers used for locale selection
     *
     * @param Request $request Current HTTP request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Loads localization settings and registers the selected translation reader
     *
     * @return void
     * @throws \Throwable If configuration or translation loading fails
     */
    public function run(): void
    {
        $detector = new XmlDetector(
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::XML_PATH
        );
        $wrapper = new Wrapper(
            $detector->getXML(),
            $this->request->parameters(),
            $this->request->headers()
        );
        ServiceRegistry::set(Reader::class, $wrapper->getReader());
    }
}
