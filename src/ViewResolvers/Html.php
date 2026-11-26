<?php

namespace Lucinda\Project\ViewResolvers;

use Lucinda\Framework\XmlDetector;
use Lucinda\MVC\Response\ViewResolver;
use Lucinda\MVC\Response\View;
use Lucinda\Templating\Wrapper;

/**
 * Compiles an HTML view using the configured view language
 *
 * Each resolve call renders the supplied view. Code quality and architecture
 * verification belong to external tools and are not part of rendering.
 */
final class Html implements ViewResolver
{
    /** Project-relative configuration path without its XML extension */
    public const XML_PATH = "xml/templating";

    /**
     * Loads templating configuration and compiles the selected HTML view
     *
     * @param View $view Template name and application data to render
     * @return string Compiled HTML response body
     * @throws \Lucinda\Framework\XmlException If configuration cannot be loaded
     * @throws \Throwable If template compilation fails
     */
    public function resolve(View $view): string
    {
        $detector = new XmlDetector(
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::XML_PATH
        );
        $wrapper = new Wrapper($detector->getXML());
        return $wrapper->compile($view->getFile(), $view->getData());
    }
}
