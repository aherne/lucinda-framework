<?php
require_once("application/controllers/AbstractLoggedInController.php");
require_once("application/models/dao/Bugs.php");
class BugsInfoController extends AbstractLoggedInController {
	protected function service() {
		// set status
		$obj = new Bugs();
		$this->response->setAttribute("info", $obj->getDetails($_GET["id"]));
	}
}