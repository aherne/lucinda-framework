<?php
/**
 * Implements a view resolver for application/json responses.
 */
class JsonWrapper extends Wrapper {
    public function run() {
        echo json_encode(array("status"=>"ok","body"=>$this->objResponse->toArray()));
    }
}