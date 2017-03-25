<?php
/**
 * Struct collecting all information about environment in which bug has occurred.
 */
class BugEnvironment {
	public $files;
	public $get;
	public $post;
	public $server;
	public $cookie;
	public $session;
}