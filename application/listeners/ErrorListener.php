<?php
require_once("libraries/php-errors-api/loader.php");

class ErrorListener extends ApplicationListener {
	public function run() {
		$this->getStorageMediums();
		
		$errorHandler = new ErrorHandler();
		$errorHandler->addStorage(new DatabaseStore());
		$errorHandler->setDisplay(new ViewDisplay());
		PHPException::setErrorHandler($errorHandler);
		set_exception_handler(array($errorHandler,"handle"));
	}
}