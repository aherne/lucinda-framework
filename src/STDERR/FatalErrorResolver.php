<?php

namespace Lucinda\Project\STDERR;

use Lucinda\STDERR\FatalErrorResolver as STDERRFatalErrorResolver;

/**
 * Renders diagnostics when normal error resolution cannot complete
 *
 * Construction loads the default output format. CLI and non-HTML requests use
 * JSON; HTML requests use mandatory minimal templates without invoking the view
 * language. Missing configuration or templates terminate immediately. HTML
 * diagnostic values are escaped before interpolation.
 */
final class FatalErrorResolver implements STDERRFatalErrorResolver
{
    /**
     * Whether application configuration selects HTML by default
     */
    private bool $isHtmlExpected;
    /**
     * Whether this resolver was constructed under the CLI SAPI
     */
    private bool $isConsoleExpected;
    /**
     * Loaded HTML page template, or null for JSON output
     */
    private ?string $templateBody = null;
    /**
     * Loaded HTML exception fragment, or null for JSON output
     */
    private ?string $templateThrowable = null;

    /**
     * Loads the fallback format and required HTML templates
     *
     * @param string $xmlPath Application XML path without its extension
     */
    public function __construct(string $xmlPath)
    {
        $fullXMLPath = $xmlPath . ".xml";
        if (!file_exists($fullXMLPath)) {
            die("XML that contains default resolver is mandatory: "
                . $fullXMLPath);
        }

        $xml = simplexml_load_file($fullXMLPath);
        if ($xml === false) {
            die("Invalid error reporting XML: " . $fullXMLPath);
        }

        $this->isHtmlExpected =
            (string) $xml->application["default_format"] === "html";
        $this->isConsoleExpected = (PHP_SAPI == "cli");
        if (!$this->isConsoleExpected && $this->isHtmlExpected) {
            $this->templateBody = $this->loadRequired("debug-fatal");
            $this->templateThrowable = $this->loadRequired(
                "debug-fatal-throwable"
            );
        }
    }

    /**
     * Reads a fallback template or terminates if it is unavailable
     *
     * @param string $name Template basename without extension
     * @return string Required template contents
     */
    private function loadRequired(string $name): string
    {
        $file = $this->getFile($name);
        $contents = is_file($file) ? file_get_contents($file) : false;

        if ($contents === false) {
            die("Fatal error template is mandatory: " . $file);
        }

        return $contents;
    }

    /**
     * Renders the current failure and an optional preceding failure
     *
     * @param \Throwable $exception Failure that prevented normal error handling
     * @param \Throwable|null $previous Original failure, when supplied
     * separately
     * @return string JSON diagnostics or an escaped HTML fallback page
     */
    public function resolve(
        \Throwable $exception,
        ?\Throwable $previous = null
    ): string {
        $payload = $this->getPayload($exception, $previous);
        if ($this->isConsoleExpected || !$this->isHtmlExpected) {
            return json_encode(
                $payload,
                JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_SLASHES
            );
        }

        return $this->interpolate(
            $this->templateBody,
            $this->templateThrowable,
            $payload
        );
    }

    /**
     * Collects the current exception and optional original failure
     *
     * @param \Throwable $exception Current fallback exception
     * @param \Throwable|null $previous Original failure, when available
     * @return array<string, mixed> Diagnostic values and optional previous
     * details
     */
    private function getPayload(
        \Throwable $exception,
        ?\Throwable $previous = null
    ): array {
        $output = $this->throwable2array($exception);
        if ($previous !== null) {
            $output["previous"] = $this->throwable2array($previous);
        }
        return $output;
    }

    /**
     * Gets the absolute path to a minimal fallback template
     *
     * @param string $name Template basename without extension
     * @return string Absolute HTML template path
     */
    private function getFile(string $name): string
    {
        return dirname(__DIR__, 2) .
            DIRECTORY_SEPARATOR . "templates" .
            DIRECTORY_SEPARATOR . "views" .
            DIRECTORY_SEPARATOR . $name . ".html";
    }

    /**
     * Extracts diagnostic values without invoking normal view rendering
     *
     * @param \Throwable $exception Exception being described
     * @return array<string, int|string> Exception type, location, message and
     * trace
     */
    private function throwable2array(\Throwable $exception): array
    {
        return [
            "type" => get_class($exception),
            "file" => $exception->getFile(),
            "line" => $exception->getLine(),
            "message" => $exception->getMessage(),
            "trace" => $exception->getTraceAsString()
        ];
    }

    /**
     * Inserts exception fragments into the minimal page template
     *
     * @param string $templateBody Page template with exception placeholders
     * @param string $templateDetails Fragment used to render each exception
     * @param array<string, mixed> Current and optional previous diagnostics
     * @return string HTML page containing escaped exception fragments
     */
    private function interpolate(
        string $templateBody,
        string $templateDetails,
        array $payload
    ): string {
        return strtr($templateBody, [
            "{error}" => $this->array2string($templateDetails, $payload),
            "{previous}" => isset($payload["previous"])
                ? $this->array2string($templateDetails, $payload["previous"])
                : "",
        ]);
    }

    /**
     * Interpolates escaped values into one exception fragment
     *
     * @param string $template Exception fragment with diagnostic placeholders
     * @param array<string, int|string> Exception diagnostic values
     * @return string HTML fragment with escaped values and formatted trace
     */
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

    /**
     * Escapes diagnostic text for safe insertion into HTML
     *
     * @param string $value Exception value to encode
     * @return string UTF-8 HTML text with invalid input replaced
     */
    private function escape(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            "UTF-8"
        );
    }
}
