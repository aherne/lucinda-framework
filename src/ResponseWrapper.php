<?php
namespace OAuth2;

/**
 * Defines response wrapping methods.
 */
interface ResponseWrapper {
	/**
	 * Wraps responce recived from OAuth2 server.
	 * 
	 * @param string $response
	 */
	function wrap($response);
}