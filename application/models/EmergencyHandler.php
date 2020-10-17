<?php
/**
 * Error handler that prevents STDERR MVC FrontController handling its own errors. Developers may need to modify contents of handle method 
 * to give more or less information about bug encountered.
 */
class EmergencyHandler implements \Lucinda\STDERR\ErrorHandler
{
    const XML_FILE_NAME = "stderr.xml";
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDERR\ErrorHandler::handle()
     */
    public function handle(\Throwable $exception): void
    {
        $application = $this->getApplicationTag();
        $displayErrors = (string) $application->display_errors->{ENVIRONMENT};
        $defaultFormat = (string) $application["default_format"];
        $this->render($exception, $defaultFormat, $displayErrors);
    }
    
    /**
     * Gets object pointing to <application> XML tag
     * 
     * @return SimpleXMLElement
     */
    private function getApplicationTag(): SimpleXMLElement
    {
        $xml = simplexml_load_file(dirname(dirname(__DIR__))."/".self::XML_FILE_NAME);
        if (!file_exists($xml)) {
            die("XML file not found: ".self::XML_FILE_NAME);
        }
        $application = simplexml_load_file($xml)->application;
        if ($referencedXML = (string) $application["ref"]) {
            $referencedXMLLocation = dirname(dirname(__DIR__))."/".$referencedXML.".xml";
            if (!file_exists($referencedXMLLocation)) {
                die("XML file not found: ".$referencedXML.".xml");
            }
            $application = simplexml_load_file($referencedXMLLocation)->application;
        }
        return $application;
    }
    
    /**
     * Renders response back to caller 
     * 
     * @param \Throwable $exception
     * @param string $defaultFormat
     * @param string $displayErrors
     */
    private function render(\Throwable $exception, string $defaultFormat, string $displayErrors): void
    {
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
