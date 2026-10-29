<?php

namespace Lucinda\Project\Controllers;

use Lucinda\MVC\Controller;
use Lucinda\MVC\Controller\ViewAware;
use Lucinda\MVC\Response\View;
use Lucinda\MVC\XmlTags\ApplicationInfo;
use Lucinda\Project\STDERR\DisplayErrors;use Lucinda\STDERR\Request;
use Lucinda\STDERR\Service\ValidatedRequest;

class Error implements ViewAware
{
    private bool $displayErrors;
    private \Throwable $handledThrowable;
    private ValidatedRequest $validatedRequest;

    public function __construct(
        DisplayErrors $displayErrors,
        Request $request,
        ValidatedRequest $validatedRequest
        )
    {
        $this->displayErrors = $displayErrors->shouldDisplayErrors();
        $this->handledThrowable = $request->getException();
        $this->validatedRequest = $validatedRequest;
    }

    public function run(): View
    {
        $format = $this->validatedRequest->getFormat();
        if ($format === "html") {
            return $this->handleHTML();
        } elseif ($format === "json") {
            return $this->handleJson();
        } elseif ($$format === "console") {
            return $this->handleConsole();
        } else {
            throw new \Exception("Unsupported content type!");
        }
    }

    private function handleHTML(): View
    {
        if ($this->displayErrors) {
            return new View($this->compileData($this->handledThrowable), "debug");
        } else {
            return new View([], "500");
        }
    }

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

    private function handleConsole(): View
    {
         if ($this->displayErrors) {
            return new View($this->compileData($this->handledThrowable), "debug-console");
        } else {
            return new View($this->compileData($this->handledThrowable), "500-console");
        }
    }

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
        $data["trace"] = $exception->getTraceAsString();
        return $data;
    }
}