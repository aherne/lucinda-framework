<?php
require_once("application/controllers/AbstractLoggedInController.php");
require_once("application/models/dao/Departments.php");
require_once("application/models/dao/Levels.php");
require_once("application/models/dao/Resources.php");

class PanelsController extends AbstractLoggedInController {
	protected function service() {
		// set users
		$panels = new Panels();
		$this->response->setAttribute("panels", $panels->getAllDetailed());

		// set resources
		$resources = new Resources();
		$this->response->setAttribute("resources", $resources->getAllDetailed());

		// set departments
		$departments = new Departments();
		$this->response->setAttribute("departments", $departments->getAllBasic());

		// set levels
		$levels = new Levels();
		$this->response->setAttribute("levels", $levels->getAllBasic());
	}
}