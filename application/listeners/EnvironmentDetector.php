<?php
/**
 * Detects development environment application runs into (eg: local, dev, live) based on value of environment variable set in .htaccess or
 * Apache2/NginX configuration files.
 */
class EnvironmentDetector  extends Lucinda\MVC\STDOUT\ApplicationListener {  
    public function run() {
        $this->application->attributes()->set("environment", getenv("ENVIRONMENT"));
    }
}