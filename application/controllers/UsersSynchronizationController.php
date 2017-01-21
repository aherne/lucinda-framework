<?php
require ("libraries/fata/src/Client.php");
require ("application/models/UsersSynchronization.php");

class UsersSynchronizationController extends Controller {
	public function run() {
		$us = new UsersSynchronization($this->application->getAttribute("authorization_code"));
		$us->synchronize();
	}
}