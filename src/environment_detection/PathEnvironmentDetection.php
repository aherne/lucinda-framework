<?php
require_once("EnvironmentDetection.php");

/**
 * Implements environment detection by disk path. Environment is successfully detected if current project disk path
 * starts with an entry in application.environments XML tag.
 */
class PathEnvironmentDetection extends EnvironmentDetection {
    protected function isMatch($value) {
        return strpos(__FILE__, $value)===0;
    }
}