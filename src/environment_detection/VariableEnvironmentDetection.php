<?php
require_once("EnvironmentDetection.php");

/**
 * Implements environment detection by ENVIRONMENT variable set in htaccess or Apache2/Nginx configs. Environment is
 * successfully detected if an entry in application.environments XML tag is identical to value of ENVIRONMENT variable.
 */
class VariableEnvironmentDetection extends EnvironmentDetection {
    const VARIABLE_NAME = "ENVIRONMENT";

    protected function isMatch($value) {
        return getenv(self::VARIABLE_NAME)==$value;
    }
}

