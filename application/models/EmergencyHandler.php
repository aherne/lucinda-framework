<?php
/**
 * Error handler that prevents STDERR MVC FrontController handling its own errors. Developers may need to modify contents of handle method to give more or
 * less information about bug encountered.
 */
class EmergencyHandler implements \Lucinda\MVC\STDERR\ErrorHandler
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDERR\ErrorHandler::handle()
     */
    public function handle($exception)
    {
        $rootXMLFile = dirname(dirname(__DIR__))."/stderr.xml";
        if (!file_exists($rootXMLFile)) {
            die("XML file not found: stderr.xml");
        }
        $application = simplexml_load_file($rootXMLFile)->application;
        if ($referencedXML = (string) $application["ref"]) {
            $referencedXMLLocation = dirname(dirname(__DIR__))."/".$referencedXML.".xml";
            if (!file_exists($referencedXMLLocation)) {
                die("XML file not found: ".$referencedXML);
            }
            $application = simplexml_load_file($referencedXMLLocation)->application;
        }
        $displayErrors = $application->display_errors->{ENVIRONMENT};
        require(dirname(__DIR__)."/views/".($displayErrors?"debug.php":"500.html"));
        die();
    }
}
