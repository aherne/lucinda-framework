<?php
class SecurityListener extends RequestListener {
	public function run() {
		$this->setAuthentication();
	}
	
	private function setAuthentication() {
		$xml = $this->application->getXML()->security->authentication;
		if(empty($xml)) throw new SecurityException("Entry missing in configuration.xml: security.authentication");
		
		if($xml->form) {
			
		}
		
	}
}