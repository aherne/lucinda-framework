<?php
/**
 * Struct collecting information about a single bug.
 */
class BugInformation {
	/**
	 * @var BugEnvironment The environment in which bug has occurred.
	 */
	public $environment;
	/**
	 * @var Exception Native PHP object encapsulating error details.
	 */
	public $exception;
	/**
	 * @var double Time at which error has occurred.
	 */
	public $time;
}