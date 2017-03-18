<?php
class SysLogger  implements ErrorReporter {
	private $applicationName;
	
	public function __construct($applicationName) {
		$this->applicationName = $applicationName;
	}
	
	public function debug($message) {
		$trace = debug_backtrace()[0];
		$line = date("Y-m-d H:i:s")."\t"."DEBUG"."\t".$trace["file"]."\t".$trace["line"]."\t".$trace["message"]."\n";
		openlog($this->applicationName, LOG_NDELAY, LOG_USER);
		syslog(LOG_DEBUG, $line);
		closelog();
	}
	
	public function report(Exception $exception) {
		$line = date("Y-m-d H:i:s")."\t".get_class($exception)."\t".$exception->getFile()."\t".$exception->getLine()."\t".$exception->getMessage()."\n";
		openlog($this->applicationName, LOG_NDELAY, LOG_USER);
		syslog(LOG_ERR, $line);
		closelog();
	}
}