<?php
require_once("application/controllers/AbstractLoggedInController.php");
require_once("application/models/dao/Departments.php");
require_once("application/models/dao/Levels.php");

class UsersController extends AbstractLoggedInController {
	protected function service() {
		// set users
		$users = new Users();
		$this->response->setAttribute("users", $users->getAllDetailed());

		// set departments
		$departments = new Departments();
		$this->response->setAttribute("departments", $departments->getAllBasic());

		// set levels
		$levels = new Levels();
		$this->response->setAttribute("levels", $levels->getAllBasic());

		// set status
		$this->response->setAttribute("status",(isset($_GET["status"])?$_GET["status"]:""));
	}
}