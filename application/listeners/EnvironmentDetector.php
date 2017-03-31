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
	public function run() {
		$this->application->setAttribute("environment", $this->getEnvironment());
	}
	
	private function getEnvironment() {		
		$tblTMP = (array) $this->application->getXML()->application->environments;
		if(empty($tblTMP)) throw new ServletException("Environments not configured!");
		foreach($tblTMP as $environmentName=>$value1) {
			if(is_array($value1)) { // it is allowed to have multiple server names per environment
				foreach($value1 as $value2) {
					if($_SERVER["SERVER_NAME"]==$value2) {
						return $environmentName;
					}
				}
			} else {
				if($_SERVER["SERVER_NAME"]==$value1) {
					return $environmentName;
				}
			}
		}
		throw new ServletException("Environment not recognized for: ".$_SERVER["SERVER_NAME"]);
	}
}