<?php
namespace OAuth2;

/**
 * Encapsulates an authorization code request according to RFC6749
 */
class AuthorizationCodeRequest implements Request {
	protected $endpointURL;
	protected $clientInformation;
	protected $redirectURL;
	protected $scope;
	protected $state;
	
	/**
	 * (Mandatory) Sets URL of authorization code endpoint @ Oauth2 Server
	 * 
	 * @param string $endpointURL
	 */
	public function __construct($endpointURL) {
		$this->endpointURL = $endpointURL;
	}
	
	/**
	 * (Mandatory) Sets client information.
	 * 
	 * @param string $clientInformation
	 */
	public function setClientInformation(ClientInformation $clientInformation) {
		$this->clientInformation = $clientInformation;
	}
	
	/**
	 * Sets callback redirect URL to send code to.
	 * 
	 * @param string $clientID
	 */
	public function setRedirectURL($redirectURL) {
		$this->redirectURL = $redirectURL;
	}
	
	/**
	 * Sets scope of access request.
	 * 
	 * @param string $scope
	 */
	public function setScope($scope) {
		$this->scope = $scope;
	}
	
	/**
	 * Sets opaque value used by the client to maintain state between the request and callback
	 * 
	 * @param string $scope
	 */
	public function setState($state) {
		$this->state = $state;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \OAuth2\Request::execute()
	 */
	public function execute(RequestExecutor $executor) {
		if(!$this->clientInformation || !$this->clientInformation->getApplicationID()) {
			throw new ClientException("Client ID is required for authorization code requests!");
		}
		$parameters = array();
		$parameters["response_type"] = "code";
		$parameters["client_id"] = $this->clientInformation->getApplicationID();
		if($this->redirectURL) 	$parameters["redirect_uri"] = $this->redirectURL;
		if($this->scope) 		$parameters["scope"] = $this->scope;
		if($this->state) 		$parameters["state"] = $this->state;
		$executor->execute($this->endpointURL, $parameters);
	}
}