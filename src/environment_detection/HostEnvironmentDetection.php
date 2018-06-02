<?php
require_once("EnvironmentDetection.php");

/**
 * Implements environment detection by domain name. Environment is successfully detected if an entry in
 * application.environments XML tag is identical match to domain name.
 */
class HostEnvironmentDetection extends EnvironmentDetection {
    protected function isMatch($value) {
        return $_SERVER['SERVER_NAME']==$value;
    }
}

