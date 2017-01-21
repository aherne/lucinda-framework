<?php
require_once("application/controllers/AbstractLoggedInController.php");
class IndexController extends AbstractLoggedInController {
    protected function service() {
        $this->response->setAttribute("pageTitle", "Account settings");
    }
}