<?php

namespace Lucinda\Project\ViewResolvers;

use Lucinda\ConsoleViewLanguage\RenderMode;
use Lucinda\ConsoleViewLanguage\Wrapper as ConsoleWrapper;
use Lucinda\Framework\XmlDetector;
use Lucinda\Templating\Wrapper as TemplatingWrapper;
use Lucinda\MVC\Response\View;
use Lucinda\MVC\Response\ViewResolver;

/**
 * Compiles a view and renders its console markup as ANSI text
 *
 * Each resolve call renders the supplied view. Code quality and architecture
 * verification belong to external tools and are not part of rendering.
 */
final class Console implements ViewResolver
{
    /** Project-relative configuration path without its XML extension */
    public const XML_PATH = "xml/templating-console";

    /**
     * Compiles the template, then converts console tags to ANSI sequences
     *
     * @param View $view Template name and application data to render
     * @return string Compiled console response body
     * @throws \Lucinda\Framework\XmlException If configuration cannot be loaded
     * @throws \Throwable If template or console markup compilation fails
     */
    public function resolve(View $view): string
    {
        $detector = new XmlDetector(
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::XML_PATH
        );
        $wrapper1 = new TemplatingWrapper($detector->getXML());
        $output = $wrapper1->compile($view->getFile(), $view->getData());

        // compile console view language
        $wrapper2 = new ConsoleWrapper($output, RenderMode::ANSI);
        return $wrapper2->getBody();
    }
}
