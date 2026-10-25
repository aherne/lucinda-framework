<?php

namespace Lucinda\Project\STDERR;

use Lucinda\STDERR\FatalErrorResolver AS STDERRFatalErrorResolver;

final class FatalErrorResolver implements STDERRFatalErrorResolver
{
    private bool $isHtmlExpected;
    private bool $isConsoleExpected;
    private ?string $templateBody = null;
    private ?string $templateThrowable = null;

    public function __construct(string $xmlPath)
    {
        $fullXMLPath = $xmlPath. ".xml";
        if (!file_exists($fullXMLPath)) {
            die("XML that contains default resolver is mandatory: ".$fullXMLPath);
        }

        $xml = simplexml_load_file($fullXMLPath);
        if ($xml === false) {
            die("Invalid error reporting XML: " . $fullXMLPath);
        }

        $this->isHtmlExpected = (string) $xml->application["default_format"] === "html";
        $this->isConsoleExpected = (PHP_SAPI=="cli");
        if (!$this->isConsoleExpected && $this->isHtmlExpected) {
            $this->templateBody = $this->loadRequired("debug-fatal");
            $this->templateThrowable = $this->loadRequired("debug-fatal-throwable");
        }
    }

    private function loadRequired(string $name): string
    {
        $file = $this->getFile($name);
        $contents = is_file($file) ? file_get_contents($file) : false;

        if ($contents === false) {
            die("Fatal error template is mandatory: ".$file);
        }

        return $contents;
    }

    public function resolve(\Throwable $exception, ?\Throwable $previous = null): string
    {
        $payload = $this->getPayload($exception, $previous);
        if ($this->isConsoleExpected || !$this->isHtmlExpected) {
            return json_encode(
                $payload,
                JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_SLASHES
            );
        }

        return $this->interpolate($this->templateBody, $this->templateThrowable, $payload);
    }

    private function getPayload(\Throwable $exception, ?\Throwable $previous = null): array
    {
        $output = $this->throwable2array($exception);
        if ($previous!==null) {
            $output["previous"] = $this->throwable2array($previous);
        }
        return $output;
    }

    private function getFile(string $name): string
    {
        return dirname(__DIR__,2).
            DIRECTORY_SEPARATOR."templates".
            DIRECTORY_SEPARATOR."views".
            DIRECTORY_SEPARATOR.$name.".html";
    }

    private function throwable2array(\Throwable $exception): array
    {
        return [
            "type"=>get_class($exception),
            "file"=>$exception->getFile(),
            "line"=>$exception->getLine(),
            "message"=>$exception->getMessage(),
            "trace"=>$exception->getTraceAsString()
        ];
    }

    private function interpolate(string $templateBody, string $templateDetails, array $payload): string
    {
        return strtr($templateBody, [
            "{error}" => $this->array2string($templateDetails, $payload),
            "{previous}" => isset($payload["previous"])
                ? $this->array2string($templateDetails, $payload["previous"])
                : "",
        ]);
    }

    private function array2string(string $template, array $payload): string
    {
        return strtr($template, [
            "{type}" => $this->escape($payload["type"]),
            "{file}" => $this->escape($payload["file"]),
            "{line}" => (string) $payload["line"],
            "{message}" => $this->escape($payload["message"]),
            "{trace}" => nl2br($this->escape($payload["trace"])),
        ]);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            "UTF-8"
        );
    }
}
