<?php
class PanelDeleteController extends Controller {
    public function run() {
        $users = new Panels();
        $users->delete($_POST["id"]);
    }
}