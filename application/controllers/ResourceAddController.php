<?php
require_once("application/models/dao/Resources.php");

class ResourceAddController extends Controller {
    public function run() {
        $resources = new Resources();
        $resourceID = $resources->add($_POST["name"]);
        
        $panels = new Panels();
        $panels->addResource($_POST["panel_id"], $resourceID);

        $departments = explode(";", $_POST["departments"]);
        foreach($departments as $part) {
            if(!$part) continue;
            $department_id = substr($part,0,strpos($part, ":"));
            $level_id = substr($part,strpos($part, ":")+1);
            $resources->addRight($resourceID, $department_id, $level_id);
        }
    }
}