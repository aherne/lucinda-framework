<?php
namespace Lucinda\Project;

use Lucinda\STDERR\Application;
use Lucinda\STDERR\ErrorHandler;
use Lucinda\MVC\Response;

/**
 * Handles errors inside STDERR MVC API. Developers may need to modify this class if XML_FILE_NAME is different or
 * to support different response formats than HTML and JSON
 */
class EmergencyHandler implements ErrorHandler
{
    public const XML_FILE_NAME = "stderr.xml";

    /**
     * {@inheritDoc}
     * @see \Lucinda\STDERR\ErrorHandler::handle()
     */
    public function handle(\Throwable $exception): void
    {
        try {
            $application = new Application(dirname(__DIR__)."/".self::XML_FILE_NAME, ENVIRONMENT);
            $defaultFormat = $application->getDefaultFormat();
            $displayErrors = $application->getDisplayErrors();
            if ($defaultFormat=="html") {
                $this->html($exception, $displayErrors);
            } else if ($defaultFormat=="json") {
                $this->json($exception, $displayErrors);
            } else {
                die("Format not supported: ".$defaultFormat);
            }
        } catch (\Throwable $e) {
            die("STDERR could not render response: ".$e->getMessage());
        }
    }
    
    /**
     * Renders response in HTML format
     * 
     * @param \Throwable $exception
     * @param bool $displayErrors
     */
    private function html(\Throwable $exception, bool $displayErrors): void
    {
        $response = new Response("text/html", dirname(__DIR__)."/templates/views/".($displayErrors ? "debug" : "500").".html");
        $response->setStatus(500);
        $contents = file_get_contents($response->view()->getFile());
        if ($displayErrors) {
            $contents = str_replace([
                '${data.class}',
                '${nl2br(${data.message})}',
                '${data.file}',
                '${data.line}',
                '${nl2br(${data.trace})}'
            ], [
                get_class($exception),
                str_replace("\\n", "<br/>", $exception->getMessage()),
                $exception->getFile(),
                $exception->getLine(),
                str_replace("\\n", "<br/>", $exception->getTraceAsString())
            ], $contents);
        }
        $response->setBody($contents);
        $response->commit();
    }
    
    /**
     * Renders response in JSON format
     * 
     * @param \Throwable $exception
     * @param bool $displayErrors
     */
    private function json(\Throwable $exception, bool $displayErrors): void
    {
        $body = [];
        if ($displayErrors) {
            $body = [
                "class"=>get_class($exception),
                "message"=>$exception->getMessage(),
                "file"=>$exception->getFile(),
                "line"=>$exception->getLine(),
                "trace"=>$exception->getTraceAsString()
            ];
        }
        $response = new Response("application/json", "");
        $response->setBody(json_encode(["status"=>"error", "body"=>$body]));
        $response->setStatus(500);
        $response->commit();
    }
}
