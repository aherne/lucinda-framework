<?php
namespace OAuth2;

/**
 * Encapsulates an access token request according to RFC6749
 */
class AccessTokenRequest implements Request {
	protected $endpointURL;
	protected $clientInformation;
	protected $redirectURL;
	protected $code;
	
	/**
	 * (Mandatory) Sets URL of access token endpoint @ Oauth2 Server
	 * 
	 * @param string $endpointURL
	 */
	public function __construct($endpointURL) {
		$this->endpointURL = $endpointURL;
	}
	
	/**
	 * (Mandatory) Sets authorization code
	 * 
	 * @param string $code
	 */
	public function setCode($code) {
		$this->code = $code;
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
	 * (Optional) Sets callback redirect URL to send access token to.
	 * 
	 * @param string $redirectURL
	 */
	public function setRedirectURL($redirectURL) {
		$this->redirectURL = $redirectURL;
	}

	/**
	 * {@inheritDoc}
	 * @see \OAuth2\Request::execute()
	 */
	public function execute(RequestExecutor $executor) {
		if(!$this->clientInformation || !$this->clientInformation->getApplicationID()) {
			throw new ClientException("Client ID is required for access token requests!");
		}
		if(!$this->code) {
			throw new ClientException("Authorization code is required for access token requests!");
		}
		$parameters = array();
		$parameters["grant_type"] = "authorization_code";
		$parameters["client_id"] = $this->clientInformation->getApplicationID();
		$parameters["code"] = $this->code;
		if($this->clientInformation->getApplicationSecret()) {
			$parameters["client_secret"] = $this->clientInformation->getApplicationSecret();
		}
		if($this->redirectURL) {
			$parameters["redirect_uri"] = $this->redirectURL;
		}
		$executor->execute($this->endpointURL, $parameters);
	}
}