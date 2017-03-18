<?php
abstract class DiskLogger implements ErrorReporter {
	public function debug($message) {
		$trace = debug_backtrace()[0];
		$line = date("Y-m-d H:i:s")."\t"."DEBUG"."\t".$trace["file"]."\t".$trace["line"]."\t".$trace["message"];
		$this->save($line, LOG_DEBUG);
	}
	
	public function report(Exception $exception) {
		$line = date("Y-m-d H:i:s")."\t".get_class($exception)."\t".$exception->getFile()."\t".$exception->getLine()."\t".$exception->getMessage();
		$this->save($line, LOG_ERR);
	}
	
	abstract protected function save($message, $logLevel);
}