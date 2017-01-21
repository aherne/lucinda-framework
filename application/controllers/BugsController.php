<?php
require_once("application/controllers/AbstractLoggedInController.php");
require_once("application/models/dao/Bugs.php");
class BugsController extends AbstractLoggedInController {
    const LIMIT = 20;
    protected function service() {
        // calculate offset
        $offset = 0;
        if(isset($_GET["page"])) {
            $offset = $_GET["page"]*self::LIMIT;
            $this->response->setAttribute("page", $_GET["page"]);
        } else {
            $this->response->setAttribute("page", 0);
        }
        $this->response->setAttribute("limit", self::LIMIT);
        
        // set status
        $obj = new Bugs();
        $this->response->setAttribute("bugs", $obj->getAll(self::LIMIT, $offset));
    }
}