<?php
/**
 * Detects environment application is running into by matching value of $_SERVER['SERVER_NAME'] with {SERVER_NAME} @ XML found here:
 * <application>
 * 		...
 * 		<environments>
 *			<{ENVIRONMENT_NAME}>{SERVER_NAME}</{ENVIRONMENT_NAME}>
 * 			...
 * 		</environments>
 * </application>
 * 
 * Results of detection will be made available across application as "environment" application attribute.
 * 
 * @attribute environment 
 */
class EnvironmentDetector  extends ApplicationListener {
    const DEFAULT_METHOD = "host";
    
	public function run() {
	    // identifies detection method
	    $detectionMethod = (string) $application->getXML()->application->environments["detection_method"];
	    if(!$detectionMethod) $detectionMethod = self::DEFAULT_METHOD;
	    
	    // detects and loads matching EnvironmentDetection class 
	    $className = ucwords(strtolower($detectionMethod))."EnvironmentDetection";
	    $fileName = "src/environment_detection/".$className.".php";
	    if(!file_exists($fileName)) throw new ApplicationException("Unrecognized environment detection method: ".$detectionMethod);
	    require_once("src/environment_detection/".$className.".php");
	    
	    // instances class and injects result into application object
	    $object = new $className($this->application);	    
	    $this->application->setAttribute("environment", $object->getEnvironment());
	}
}