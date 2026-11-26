<?php

namespace Lucinda\Project\ViewResolvers;

use Lucinda\MVC\Response\ViewResolver;
use Lucinda\MVC\Response\View;
use Lucinda\Framework\Json as JsonConverter;

/**
 * Encodes view data in the framework JSON response envelope
 *
 * Each resolve call renders the supplied view. Code quality and architecture
 * verification belong to external tools and are not part of rendering.
 */
final class Json implements ViewResolver
{
    /**
     * Encodes application data and marks responses from STDERR as errors
     *
     * The error marker is selected from the invoking errors-mvc source path.
     * Other callers produce the normal success envelope.
     *
     * @param View $view Application values to place in the response body
     * @return string JSON object with status and body members
     * @throws \Throwable If the JSON converter cannot encode the payload
     */
    public function resolve(View $view): string
    {
        // see who triggered resolver (tested to have zero performance impact)
        $isError = str_contains(
            str_replace("\\", "/", debug_backtrace()[0]["file"]),
            "vendor/lucinda/errors-mvc/"
        );

        // resolves response in json format
        $json = new JsonConverter();
        return $json->encode(
            [
            "status" => (!$isError ? "ok" : "error"),
            "body" => $view->getData()
            ]
        );
    }
}
