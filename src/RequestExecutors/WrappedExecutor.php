<?php
namespace OAuth2;

/**
 * Implements an executor on top of cURL implementing OAuth2 request execution rules in accordance to RFC6749.
 */
class WrappedExecutor implements RequestExecutor {
	protected $responseWrapper;
	protected $headers = array('Content-Type: application/x-www-form-urlencoded');
	
	public function __construct(ResponseWrapper $responseWrapper) {
		$this->responseWrapper = $responseWrapper;
		$this->headers[]='Content-Type: application/x-www-form-urlencoded';
	}
		
	/**
	 * Adds authorization token header.
	 * 
	 * @param string $tokenType
	 * @param string $accessToken
	 */
	public function addAuthorizationToken($tokenType, $accessToken) {
		$this->headers[] = "Authorization: ".$tokenType." ".$accessToken;		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \OAuth2\RequestExecutor::execute()
	 */
	public function execute($endpointURL, $parameters) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$endpointURL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		try {
			$server_output = curl_exec ($ch);
			if($server_output===false) {
				throw new ClientException(curl_error($ch));
			}
			$this->responseWrapper->wrap($server_output);
		} finally {
			curl_close ($ch);
		}
	}
}