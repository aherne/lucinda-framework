<?php
require_once("application/models/dao/Resources.php");

class PanelAddController extends Controller {

    public function run() {
        $panels = new Panels();
        $id = $panels->add($_POST["parent_id"], $_POST["name"], $_POST["url"], $_POST["is_public"]);

        $resources = new Resources();
        $departments = explode(";", $_POST["departments"]);
        foreach($departments as $part) {
            if(!$part) continue;
            $department_id = substr($part,0,strpos($part, ":"));
            $level_id = substr($part,strpos($part, ":")+1);
            $resources->addRight($id, $department_id, $level_id);
        }
    }
}