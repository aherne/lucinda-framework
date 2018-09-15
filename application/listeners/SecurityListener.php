<?php
require_once("vendor/lucinda/security/loader.php");
require_once("vendor/lucinda/framework-engine/src/security/SecurityBinder.php");

/**
 * Sets up and performs web security in your application by binding PHP-SECURITY-API & OAUTH2-CLIENT with contents of "security" tag @ CONFIGURATION.XML, 
 * itself handled by SERVLETS API. Syntax for XML "security" tag is:
 * <security>
 * 		<csrf />
 * 		<persistence>...</persistence>
 * 		<authentication>...</authentication>
 * 		<authorization ...>...</authorization>
 * </security>
 * 
 * Where:
 * - csrf: (MANDATORY) this tag holds settings necessary to generate a CSRF token
 * - persistence: (OPTIONAL) this tag holds sub-tags that set up your state persistence driver chosen
 * - authentication: (MANDATORY) this tag holds sub-tags that set up your authentication solution chosen
 * - authorization; (MANDATORY) this tag holds sub-tags that set up your authorization solution chosen
 * 
 * For security reasons, all authentication attempts require a CSRF token. Contents of "csrf" tag are used to setup an instance of CsrfTokenDetector 
 * that is saved as "csrf" request attribute, to be used later on in generating or verifying csrf tokens.
 * 
 * The next stage is to find the state persistence method chosen (eg: session, cookie), based on contents of "persistence" tag. For each found stance, 
 * unique user id is searched for. If found, value is saved as "user_id" request attribute, to be used later on in the flow of application. For REST-ful or
 * many other web service applications where there should be no persistence, "persistence" tag is not required and authorization is done via a synchronizer token.
 * 
 * The next stage is investigating "authentication" tag. If authentication (login/logout) is requested, a SecurityPacket is thrown encapsulating details of 
 * possible redirection. If outcome is successful, "user_id" attribute is updated. If no authentication is requested, request passes through. 
 * 
 * The final stage is to investigating "authorization" tag. Client's right to requested resource is investigated depending on state (logged in or not) or 
 * discrete rights per resource (on many applications, not all logged in users have same rights to all protected resources). If authorization fails, a SecurityPacket
 * is thrown, encapsulating details of possible redirection. If authorization is successful, request passes through. 
 * 
 * @attribute csrf Stores an instance of CsrfTokenDetector to use in generating tokens.
 * @attribute user_id Stores unique user identifier (for logged in users).
 * @attribute oauth2 Stores oauth2 drivers detected (if any).
 */
class SecurityListener extends Lucinda\MVC\STDOUT\RequestListener {
	private $persistenceDrivers = array();

	public function run() {	    
	    $securityFilter = new SecurityBinder($this->application, $this->request);
	    $this->request->attributes()->set("user_id", $securityFilter->getUserID());
	    $this->request->attributes()->set("csrf", $securityFilter->getCsrfToken());
	    $this->request->attributes()->set("oauth2", $securityFilter->getOAuth2Drivers());
	}
}