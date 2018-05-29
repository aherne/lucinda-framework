<?php
require_once("EnvironmentDetection.php");

class HostEnvironmentDetection extends EnvironmentDetection {
    protected function isMatch($value) {
        return $_SERVER['SERVER_NAME']==$value;
    }
}