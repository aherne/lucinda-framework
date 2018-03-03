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
class EnvironmentDetector  extends RequestListener {
	public function run() {
		$this->application->setAttribute("environment", $this->getEnvironment());
	}
	
	private function getEnvironment() {		
		$tMP = (array) $this->application->getXML()->application->environments;
		if(empty($tMP)) throw new ApplicationException("Environments not configured!");
		foreach($tMP as $environmentName=>$value1) {
			if(is_array($value1)) { // it is allowed to have multiple server names per environment
				foreach($value1 as $value2) {
					if($this->request->getServer()->getName()==$value2) {
						return $environmentName;
					}
				}
			} else {
			    if($this->request->getServer()->getName()==$value1) {
					return $environmentName;
				}
			}
		}
		throw new ApplicationException("Environment not recognized for: ".$this->request->getServer()->getName());
	}
}