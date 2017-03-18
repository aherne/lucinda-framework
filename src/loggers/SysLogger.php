<?php
class SysLogger extends DiskLogger {
	private $applicationName;
	
	public function __construct($applicationName) {
		$this->applicationName = $applicationName;
	}
	
	protected function save($message, $logLevel) {
		openlog($this->applicationName, LOG_NDELAY, LOG_USER);
		syslog($logLevel, $message);
		closelog();
	}
}