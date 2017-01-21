<?php
require_once("application/models/dao/Resources.php");

class PanelEditController extends Controller {

    public function run() {
        $panels = new Panels();
        $panels->update($_POST["id"], $_POST["name"], $_POST["url"], $_POST["is_public"]);
        
        $resources = new Resources();
        $departments = explode(";", $_POST["departments"]);
        $resources->deleteRights($_POST["id"]);
        foreach($departments as $part) {
            if(!$part) continue;
            $department_id = substr($part,0,strpos($part, ":"));
            $level_id = substr($part,strpos($part, ":")+1);
            $resources->addRight($_POST["id"], $department_id, $level_id);
        }
    }
}