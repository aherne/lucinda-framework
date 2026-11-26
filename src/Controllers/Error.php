<?php

namespace Lucinda\Project\Controllers;

use Lucinda\MVC\Controller\ViewAware;
use Lucinda\MVC\Response\View;
use Lucinda\Project\STDERR\DisplayErrors;
use Lucinda\STDERR\Request;
use Lucinda\Templating\ViewException;
use Lucinda\STDERR\Service\ValidatedRequest;

require_once dirname(__DIR__, 2) . "/helpers/beautify.php";

/**
 * Selects public error views or debug diagnostics for the requested format
 *
 * Template diagnostics use original VL source locations when available and
 * retain the underlying PHP failure separately from the template location.
 */
final class Error implements ViewAware
{
    /**
     * Diagnostic visibility selected when the controller is constructed
     */
    private bool $displayErrors;
    /**
     * Exception whose diagnostic or public view is being selected
     */
    private \Throwable $handledThrowable;
    /**
     * Validated error route supplying the requested output format
     */
    private ValidatedRequest $validatedRequest;

    /**
     * Captures the exception, output format and diagnostic policy
     *
     * @param DisplayErrors $displayErrors Application diagnostic visibility
     * policy
     * @param Request $request Error request holding the handled exception
     * @param ValidatedRequest $validatedRequest Resolved error route and format
     */
    public function __construct(
        DisplayErrors $displayErrors,
        Request $request,
        ValidatedRequest $validatedRequest
    ) {
        $this->displayErrors = $displayErrors->shouldDisplayErrors();
        $this->handledThrowable = $request->getException();
        $this->validatedRequest = $validatedRequest;
    }

    /**
     * Selects the configured error view for the requested output format
     *
     * @return View Public error page or diagnostic data for HTML, JSON or
     * console
     * @throws \Exception If the requested output format is unsupported
     */
    public function run(): View
    {
        $format = $this->validatedRequest->getFormat();
        if ($format === "html") {
            return $this->handleHTML();
        } elseif ($format === "json") {
            return $this->handleJson();
        } elseif ($format === "console") {
            return $this->handleConsole();
        } else {
            throw new \Exception("Unsupported content type!");
        }
    }

    /**
     * Builds the HTML error view according to diagnostic visibility
     *
     * @return View Debug template with diagnostics or the public 500 template
     */
    private function handleHTML(): View
    {
        if ($this->displayErrors) {
            return new View(
                $this->compileData($this->handledThrowable),
                "debug"
            );
        } else {
            return new View([], "500");
        }
    }

    /**
     * Builds JSON error data according to diagnostic visibility
     *
     * @return View Diagnostic values or a generic internal-error message
     */
    private function handleJson(): View
    {
        $data = [];
        if ($this->displayErrors) {
            $data = $this->compileData($this->handledThrowable);
        } else {
            $data["message"] = "Internal Server Error";
        }
        return new View($data);
    }

    /**
     * Builds the console error view according to diagnostic visibility
     *
     * @return View Console debug template or the public console 500 template
     */
    private function handleConsole(): View
    {
        if ($this->displayErrors) {
            return new View(
                $this->compileData($this->handledThrowable),
                "debug-console"
            );
        } else {
            return new View([], "500-console");
        }
    }

    /**
     * Collects diagnostics shared by HTML, JSON and console debug responses
     *
     * @param \Throwable $exception Failure being reported
     * @return array<string,mixed> Exception details with optional SQL or VL
     *     context
     */
    private function compileData(\Throwable $exception): array
    {
        $data = [];
        $data["message"] = $exception->getMessage();
        $data["type"] = get_class($exception);
        $data["file"] = $exception->getFile();
        $data["line"] = $exception->getLine();
        if ($exception instanceof \Lucinda\SQL\StatementException) {
            $data["query"] = $exception->getQuery();
        }
        if ($exception instanceof ViewException) {
            if ($location = $exception->getSourceLocation()) {
                $data["template"] = [
                    "file" => $location["source"],
                    "line" => $location["line"],
                ];
                $data["file"] = $location["source"];
                $data["line"] = $location["line"];
            }
            if ($previous = $exception->getPrevious()) {
                $data["cause"] = [
                    "type" => get_class($previous),
                    "message" => $previous->getMessage(),
                    "file" => $previous->getFile(),
                    "line" => $previous->getLine(),
                    "trace" => $previous->getTraceAsString()
                ];
            }
        }
        $data["trace"] = $exception->getTraceAsString();
        return $data;
    }
}
