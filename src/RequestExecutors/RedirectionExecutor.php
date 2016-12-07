<?php
namespace OAuth2;

/**
 * Implements an executor that redirects to payload url using GET parameters
 */
class RedirectionExecutor implements RequestExecutor {
	/**
	 * {@inheritDoc}
	 * @see \OAuth2\RequestExecutor::execute()
	 */
	public function execute($url, $parameters) {
		header("Location: ".$url."?".http_build_query($parameters));
		exit();
	}
}