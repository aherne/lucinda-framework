<?php
require_once("MethodNotAllowedException.php");

/**
 * Defines an abstract RESTful controller. Classes extending it must implement methods that correspond to HTTP verbs they need.
 * 
 * Example:
 * // listens to http://lucian.com/example (PUT):
 * class ExampleController extends RestController {
 * 		public function PUT() {
 * 			// will be triggered whenever someone makes a PUT request to path listened (routed) by ExampleController
 * 		}
 * }
 */
abstract class RestController extends Lucinda\MVC\STDOUT\Controller {
	public function run() {
		$method = strtolower($this->request->getMethod());
		$this->$method();
	}
	
	protected function get() {
		throw new MethodNotAllowedException();
	}
	
	protected function post() {
		throw new MethodNotAllowedException();
	}
	
	protected function put() {		
		throw new MethodNotAllowedException();
	}
	
	protected function delete() {		
		throw new MethodNotAllowedException();
	}
	
	protected function head() {
		throw new MethodNotAllowedException();		
	}
	
	protected function options() {
		throw new MethodNotAllowedException();		
	}
	
	protected function trace() {
		throw new MethodNotAllowedException();		
	}
	
	protected function connect() {
		throw new MethodNotAllowedException();		
	}
}