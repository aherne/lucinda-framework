<?php
require_once("vendor/lucinda/framework-engine/src/security/SecurityBinder.php");

/**
 * Binds STDOUT MVC API with Web Security API + OAuth2 Client API and contents of 'security' tag @ configuration.xml
 * in order to be able to perform web security operations (authentication/authorization) to a request.
 * 
 * Sets attributes:
 * - user_id: (string|integer) unique user identifier of logged in user read from / written to persistence drivers (eg: session)
 * - csrf: (string) value of synchronizer token to be sent as part of log in form
 * - oauth2: (array[string:OAuth2\Driver]) List of detected oauth2 drivers by driver name.
 */
class SecurityListener extends \Lucinda\MVC\STDOUT\RequestListener {
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDOUT\Runnable::run()
     */
	public function run() {	    
	    $securityFilter = new Lucinda\Framework\SecurityBinder($this->application, $this->request);
	    $this->request->attributes("user_id", $securityFilter->getUserID());
	    $this->request->attributes("csrf", $securityFilter->getCsrfToken());
	    $this->request->attributes("oauth2", $securityFilter->getOAuth2Driver());
	}
}