<?php
/**
 * Defines logging blueprints.
 */
interface Logger {
	/**
	 * @param string $message Logs a message to a storage medium.
	 */
	function log($message);
}