<?php
require_once("XMLSessionSetup.php");

class LocaleDetector {
    private $detectionMethod;
    private $defaultLocale;
    private $detectedLocale;
    
    public function __construct(SimpleXMLElement $xml, Request $request) {
        $this->setDetectionMethod($xml);
        $this->setDefaultLocale($xml);
        $this->setDetectedLocale($xml, $request);
    }
    
    private function setDetectionMethod(SimpleXMLElement $xml) {
        $detectionMethod = (string) $xml["method"];
        if(!$detectionMethod) throw new ApplicationException("Attribute missing/empty in configuration.xml: internationalization['method]");
        $this->detectionMethod = $detectionMethod;
    }
    
    private function setDefaultLocale(SimpleXMLElement $xml) {
        $defaultLocale =  (string) $xml["locale"];
        if(!$defaultLocale) throw new ApplicationException("Attribute missing/empty in configuration.xml: internationalization['locale]");
        $this->defaultLocale = $defaultLocale;
    }
    
    private function setDetectedLocale(SimpleXMLElement $xml, Request $request) {
        $this->detectedLocale = $this->defaultLocale;
        switch($this->detectionMethod) {
            case "header":
                $header = $request->getHeader("Accept-Language");
                if($header) {
                    $this->detectedLocale = str_replace("-", "_", substr($header, 0, strpos($header, ",")));
                }
                break;
            case "request":
                $parameter = $request->getURI()->getParameter(self::PARAMETER_NAME);
                if($parameter) {
                    $this->detectedLocale = $parameter;
                }
                break;
            case "session":
                $session = $request->getSession();
                if(!$session->isStarted()) {
                    $tag = $xml->session;
                    if(!empty($tag)) {
                        $setup = new XMLSessionSetup($tag);
                        $session->start($setup->getSecurityOptions(), $setup->getHandler());
                    } else {
                        $session->start();
                    }
                }
                $parameter = $request->getURI()->getParameter(self::PARAMETER_NAME);
                if($parameter) {
                    $this->detectedLocale = $parameter;
                    return;
                }
                if($session->contains(self::PARAMETER_NAME)) {
                    $this->detectedLocale = $session->get(self::PARAMETER_NAME);
                }
                break;
            default:
                throw new ApplicationException("Invalid detection method: ".$this->detectionMethod);
                break;
        }
    }
    
    public function getDefaultLocale() {
        return $this->defaultLocale;
    }
    
    public function getDetectedLocale() {
        return $this->detectedLocale;
    }
    
    public function getDetectionMethod() {
        return $this->detectionMethod;
    }
}