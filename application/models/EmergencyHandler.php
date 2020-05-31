<?php
/**
 * Error handler that prevents STDERR MVC FrontController handling its own errors. Developers may need to modify contents of handle method 
 * to give more or less information about bug encountered.
 */
class EmergencyHandler implements \Lucinda\STDERR\ErrorHandler
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDERR\ErrorHandler::handle()
     */
    public function handle(\Throwable $exception): void
    {
        $xml = simplexml_load_file(dirname(dirname(__DIR__))."/stderr.xml");
        $displayErrors = (string) $xml->application->display_errors->{ENVIRONMENT};
        $defaultFormat = (string) $xml->application["default_format"];
        
        if ($defaultFormat=="html") {
            $response = new \Lucinda\STDERR\Response("text/html", dirname(__DIR__)."/views/".($displayErrors?"debug":"500").".html");
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
        } else {
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
            $response = new \Lucinda\STDERR\Response("application/json", "");
            $response->setBody(json_encode(["status"=>"error", "body"=>$body]));
            $response->setStatus(500);
            $response->commit();
        }
    }
}
