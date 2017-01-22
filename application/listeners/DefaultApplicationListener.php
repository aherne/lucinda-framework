<?php
class DefaultApplicationListener extends ApplicationListener {
	public function run() {
		// detect secret key remember me cookie will be encoded with
		$this->setSecretKey();

		// detect view compilations folder
		$this->setCompilationsFolder();
	}

	/**
	 * Sets secret key that will be use in tokens generated 
	 */
	private function setSecretKey() {
		$this->application->setAttribute("secret_key", (string) $this->application->getXML()->application->secret_key);
	}

	/**
	 * Sets View Language compilations folder
	 */
	private function setCompilationsFolder() {
		$environment = $this->application->getAttribute("environment");
		$compilationsInfo = (string) $this->application->getXML()->application->paths->compilations->$environment;
		$this->application->setAttribute("compilations_folder", $compilationsInfo);
	}
}