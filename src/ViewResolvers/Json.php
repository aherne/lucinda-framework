<?php

namespace Lucinda\Project\ViewResolvers;

use Lucinda\MVC\Response\ViewResolver;
use Lucinda\MVC\Response\View;
use Lucinda\Framework\Json as JsonConverter;

final class Json implements ViewResolver
{
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
            "status"=>(!$isError ? "ok" : "error"),
            "body"=>$view->getData()
            ]
        );
    }
}