<?php
require_once("application/models/dao/Resources.php");

class ResourceDeleteController extends Controller {
    public function run() {
        $users = new Resources();
        $users->delete($_POST["id"]);
    }
}