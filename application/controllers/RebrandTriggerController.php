<?php
require_once("application/controllers/AbstractLoggedInController.php");
require_once("application/models/dao/RebrandingTables.php");
require_once("application/models/dao/RebrandingTriggers.php");
class RebrandTriggerController  extends AbstractLoggedInController {
	protected function service() {
		if(!empty($_POST)) {
			$this->save();
		}
		$objRebrandingTables = new RebrandingTables($this->application->getAttribute("parent_schema"));
		$this->response->setAttribute("tables", $objRebrandingTables->getAll());
		
		$objRebrandingTriggers = new RebrandingTriggers($this->application->getAttribute("parent_schema"));
		$this->response->setAttribute("triggers", $objRebrandingTriggers->getAll());
	}
	
	private function save() {
		$topPicks = new RebrandingTriggers($this->application->getAttribute("parent_schema"));
		$topPicks->update($_POST["tables"]);
		$this->statusCode = "EDIT_SUCCESSFUL";
	}
}