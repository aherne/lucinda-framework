<?php
abstract class EnvironmentDetection {
    protected $environment;
    
    public function __construct(Application $application) {
        $this->setEnvironment($application);
    }
    
    private function setEnvironment(Application $application){        
        $tMP = (array) $application->getXML()->application->environments;
        if(empty($tMP)) throw new ApplicationException("Environments not configured!");
        foreach($tMP as $environmentName=>$value1) {
            if(is_array($value1)) { // it is allowed to have multiple server names per environment
                foreach($value1 as $value2) {
                    if($this->isMatch($value2)) {
                        return $environmentName;
                    }
                }
            } else {
                if($this->isMatch($value1)) {
                    return $environmentName;
                }
            }
        }
        throw new ApplicationException("Environment not recognized!");
    }
    
    abstract protected function isMatch($value);
    
    public function getEnvironment() {
        return $this->environment;
    }
}