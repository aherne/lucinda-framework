<?php
class JsonWrapper extends Wrapper {
    public function run() {
        echo json_encode(array("status"=>"ok","body"=>$this->objResponse->toArray()));
    }
}