<?php
namespace OAuth2;

/**
 * Encapsulates an OAuth2 request to server
 */
interface Request {
	/**
	 * Executes request.
	 * 
	 * @param RequestExecutor $executor Performs request execution.
	 * @throws ClientException
	 */
	public function execute(RequestExecutor $executor);
}