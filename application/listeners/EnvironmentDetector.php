<?php
/**
 * Detects development environment application runs into (eg: local, dev, live) based on value of environment variable set in .htaccess or
 * Apache2/NginX config files via setenv directive.
 * 
 * Sets attributes:
 * - environment: (string) value of development environment detected
 */
class EnvironmentDetector  extends Lucinda\MVC\STDOUT\ApplicationListener {
    /**
     * {@inheritDoc}
     * @see Lucinda\MVC\STDOUT\Runnable::run()
     */
    public function run() {
        $this->application->attributes()->set("environment", getenv("ENVIRONMENT"));
    }
}