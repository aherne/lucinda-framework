<?php
require_once("application/controllers/AbstractLoggedInController.php");
require_once("application/models/dao/Messages.php");

class MessagesController extends AbstractLoggedInController {
    protected function service() {
        // set status
        $obj = new Messages();
        $this->response->setAttribute("messages", $obj->getAll());
    }
}