<?php
/**
 * Detects environment application is running into by matching value of $_SERVER['SERVER_NAME'] with {SERVER_NAME} @ XML found here:
 * <application>
 * 		...
 * 		<environments detection_method="host/path">
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
        $this->application->setAttribute("environment", getenv("ENVIRONMENT"));
    }
}