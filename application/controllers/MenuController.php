<?php
require_once("application/controllers/AbstractLoggedInController.php");
require_once("application/models/dao/Menu.php");

class MenuController extends AbstractLoggedInController {
	protected function service() {
		if(!empty($_POST)) {
			$this->save();
		}

		$menu = new Menu();
		$this->response->setAttribute("menus", $menu->getAll());
		 
		$panels = new Panels();
		$this->response->setAttribute("panels", $panels->getMenu());
	}

	private function save() {
		$menu = new Menu();
		$menu->save($_POST["menu"]);
		$this->statusCode = "EDIT_SUCCESSFUL";
	}
}