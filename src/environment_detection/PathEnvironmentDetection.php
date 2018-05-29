<?php
require_once("EnvironmentDetection.php");

class PathEnvironmentDetection extends EnvironmentDetection {
    protected function isMatch($value) {
        return strpos(__FILE__, $value)===0;
    }
}