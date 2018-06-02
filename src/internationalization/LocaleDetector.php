<?php
require_once("XMLSessionSetup.php");

/**
 * Detects locale based on contents of internationalization tag:
 * 
 * <internationalization locale="en_US" method="session"></internationalization>
 */
class LocaleDetector {
    const PARAMETER_NAME = "locale";
    private $detectionMethod;
    private $defaultLocale;
    private $detectedLocale;
    
    /**
     * @param SimpleXMLElement $xml Internationalization tag content.
     * @param Request $request Encapsulates request information.
     */
    public function __construct(SimpleXMLElement $xml, Request $request) {
        $this->setDetectionMethod($xml);
        $this->setDefaultLocale($xml);
        $this->setDetectedLocale($xml, $request);
    }
    
    /**
     * Sets detection method based on "method" attribute of <internationalization> tag.
     * 
     * @param SimpleXMLElement $xml Internationalization tag content.
     * @throws ApplicationException If "method" attribute is missing or empty.
     */
    private function setDetectionMethod(SimpleXMLElement $xml) {
        $detectionMethod = (string) $xml["method"];
        if(!$detectionMethod) throw new ApplicationException("Attribute missing/empty in configuration.xml: internationalization['method]");
        $detectionMethod = strtolower($detectionMethod);
        if(!in_array($detectionMethod, array("header","request","session"))) throw new ApplicationException("Invalid detection method: ".$detectionMethod);
        $this->detectionMethod = $detectionMethod;
    }
    
    /**
     * Sets default locale based on "locale" attribute of <internationalization> tag.
     *
     * @param SimpleXMLElement $xml Internationalization tag content.
     * @throws ApplicationException If "locale" attribute is missing or empty.
     */
    private function setDefaultLocale(SimpleXMLElement $xml) {
        $defaultLocale =  (string) $xml["locale"];
        if(!$defaultLocale) throw new ApplicationException("Attribute missing/empty in configuration.xml: internationalization['locale]");
        $this->defaultLocale = $defaultLocale;
    }
    
    /**
     * Sets detected locale based on detection method, client request as well as <session> XML tag (if detection method is session)
     * 
     * @param SimpleXMLElement $xml  Internationalization tag content.
     * @param Request $request Encapsulates request information.
     * @throws ApplicationException If "method" attribute contains a wrong value.
     */
    private function setDetectedLocale(SimpleXMLElement $xml, Request $request) {
        $this->detectedLocale = $this->defaultLocale;
        switch($this->detectionMethod) {
            case "header":
                $header = $request->getHeader("Accept-Language");
                if($header) {
                    $locale = substr($header, 0, strpos($header, ","));
                    $slashPosition = strpos($locale, "-");
                    $this->detectedLocale = strtolower(substr($locale, 0, $slashPosition))."_".strtoupper(substr($locale, $slashPosition+1));
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
        }
    }
    
    /**
     * Gets application-generic locale detected from XML
     * 
     * @return string A combination of lowercase language code and uppercase country code. (Eg: en_US)
     */
    public function getDefaultLocale() {
        return $this->defaultLocale;
    }
    
    /**
     * Gets locale requested by client detected by matching request to XML settings
     *
     * @return string A combination of lowercase language code and uppercase country code. (Eg: en_US)
     */
    public function getDetectedLocale() {
        return $this->detectedLocale;
    }
    
    /**
     * Gets locale detection method
     * 
     * @return string Can be: header, request, session 
     */
    public function getDetectionMethod() {
        return $this->detectionMethod;
    }
}