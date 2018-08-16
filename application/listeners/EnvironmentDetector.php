<?php
/**
 * Detects development environment application runs into (eg: local, dev, live) based on value of environment variable set in .htaccess or
 * Apache2/NginX configuration files.
 */
class EnvironmentDetector  extends ApplicationListener {  
    public function run() {
        $this->application->setAttribute("environment", getenv("ENVIRONMENT"));
    }
}