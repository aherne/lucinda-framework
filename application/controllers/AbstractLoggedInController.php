<?php
require_once("application/controllers/AbstractHtmlController.php");
require_once("application/models/MenuGenerator.php");
require_once("application/models/dao/Panels.php");

abstract class AbstractLoggedInController extends AbstractHtmlController {
	protected function init() {
		parent::init();
		$this->setMenu();
		$this->setTitle();
		$this->setUserInfo();
	}

	protected function setUserInfo() {
		$users = new Users();
		$this->response->setAttribute("user", $users->getInfo($_SESSION["user_id"]));
	}

	protected function setMenu() {
		$menuGenerator = new MenuGenerator();
		$this->response->setAttribute("menu", $menuGenerator->getMenu());
	}

	protected function setTitle() {
		$panels = new Panels();
		$info = $panels->getInfoByURL($this->request->getAttribute("page_url"));
		$this->response->setAttribute("pageTitle", $info->name);
	}
}