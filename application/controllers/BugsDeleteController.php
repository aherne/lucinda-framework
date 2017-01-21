<?php
require_once("application/models/dao/Bugs.php");

class BugsDeleteController extends Controller {
    public function run() {
        $obj = new Bugs();
        $obj->delete($_POST["id"]);
    }
}