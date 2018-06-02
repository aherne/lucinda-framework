<?php
/**
 * Class responsible for runtime environment detection by matching contents of application.environments XML tag
 * with runtime environment unique identifiers (eg: host name) to be implemented by its children.
 */
abstract class EnvironmentDetection {
    protected $environment;

    /**
     * EnvironmentDetection constructor.
     *
     * @param SimpleXMLElement $xml Holds information about application detected from XML.
     * @throws ApplicationException If XML fails to hold required information to detect environment
     */
    public function __construct(SimpleXMLElement $xml) {
        $this->setEnvironment($xml);
    }

    /**
     * Matches information about application detected from XML with server or path info.
     *
     * @param SimpleXMLElement $xml Holds information about application detected from XML.
     * @throws ApplicationException If XML fails to hold required information to detect environment
     */
    private function setEnvironment(SimpleXMLElement $xml){
        $tMP = (array) $xml->application->environments;
        if(empty($tMP)) throw new ApplicationException("Environments not configured!");
        foreach($tMP as $environmentName=>$value1) {
            if(is_array($value1)) { // it is allowed to have multiple server names per environment
                foreach($value1 as $value2) {
                    if($this->isMatch($value2)) {
                        $this->environment = $environmentName;
                        return;
                    }
                }
            } else {
                if($this->isMatch($value1)) {
                    $this->environment = $environmentName;
                    return;
                }
            }
        }
        throw new ApplicationException("Environment not recognized!");
    }

    /**
     * Delegates matching entry in application.environments XML tag with runtime environment unique identifiers.
     *
     * @param string $value
     * @return boolean
     */
    abstract protected function isMatch($value);

    /**
     * Gets detected execution environment.
     *
     * @return mixed
     */
    public function getEnvironment() {
        return $this->environment;
    }
}