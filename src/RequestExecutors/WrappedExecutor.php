<?php
namespace OAuth2;

/**
 * Implements an executor on top of cURL implementing OAuth2 request execution rules in accordance to RFC6749.
 */
class WrappedExecutor implements RequestExecutor {
	protected $responseWrapper;
	protected $httpMethod = HttpMethod::POST;
	protected $headers = array('Content-Type: application/x-www-form-urlencoded');
	
	public function __construct(ResponseWrapper $responseWrapper) {
		$this->responseWrapper = $responseWrapper;
	}
	
	/**
	 * Sets request http method
	 * 
	 * @param integer $httpMethod
	 */
	public function setHttpMethod($httpMethod = HttpMethod::POST) {
		$this->httpMethod = $httpMethod;
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
		switch($this->httpMethod) {
			case HttpMethod::POST:
				curl_setopt($ch, CURLOPT_URL,$endpointURL);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
				break;
			case HttpMethod::GET:
				curl_setopt($ch, CURLOPT_URL,$endpointURL."?".http_build_query($parameters));
				break;
			case HttpMethod::PUT:
				curl_setopt($ch, CURLOPT_PUT, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
				break;
			case HttpMethod::DELETE:
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
				break;
			default:
				throw new ClientException("Unrecognized http method!");
				break;
		}
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