<?php
require_once("application/models/HtaccessScanner.php");
require_once("application/models/dao/RebrandingTriggers.php");

class HtaccessScannerController extends Controller {
	public function run() {
		// scan file
		$scanner = new HtaccessScanner($this->application->getAttribute("htaccess_location"));
		$results = $scanner->getResults();
		
		// write to database
		$rebrandingTriggers = new RebrandingTriggers($this->application->getAttribute("parent_schema"));
		$rebrandingTriggers->insert($results);
	}
}