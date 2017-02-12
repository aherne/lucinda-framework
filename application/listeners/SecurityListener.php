<?php
// require_once("application/libraries/php-security-api/loader.php");
/**
 * 
		<authentication>
			<form dao="{CLASS_PATH}">
				<login parameter_username="" parameter_password="" page="{LOGIN_URL}" target="{PAGE_AFTER_LOGIN}" parameter_rememberMe=""/>
				<logout page="{LOGOUT_URL}" target="{PAGE_AFTER_LOGOUT}"/>
			</form>
			<oauth2 dao="{CLASS_PATH}">
				<facebook page="{LOGIN_URL}" target="{PAGE_AFTER_LOGIN}">
					<app_id>{APPLICATION_ID}</app_id>
					<app_secret>{APPLICATION_SECRET}</app_secret>
					<callback_url>{URL_OF_PAGE_TO_RECEIVE_AUTHORIZATION_CODE}</callback_url>
					<scopes>{OPTIONAL|DEFAULTS_TO_USERINFO_SCOPE}</scopes>
				</facebook>
				
			</oauth2>
		</authentication>
 * @author aherne
 *
 */
class SecurityListener extends RequestListener {
	private $persistenceDrivers = array();
	
	public function run() {
		$this->setPersistenceDrivers();
		$this->authenticate();
		$this->authorize();
	}
	
	private function setPersistenceDrivers() {
		
	}
	
	private function authenticate() {
		$xml = $this->application->getXML()->security->authentication;
		if(empty($xml)) throw new ServletApplicationException("Entry missing in configuration.xml: security.authentication");
		$currentPage = $this->request->getAttribute("page_url");
		
		if($xml->form) {
			require_once("application/models/FormAuthenticationWrapper.php");
			new FormAuthenticationWrapper($xml->form, $currentPage, $this->persistenceDrivers);
		}
		if($xml->oauth2) {
			require_once("application/models/Oauth2AuthenticationWrapper.php");
			new Oauth2AuthenticationWrapper($xml->oauth2, $currentPage, $this->persistenceDrivers);
		}
	}
	
	private function authorize() {
		$xml = $this->application->getXML()->security->authorization;
		if(empty($xml)) throw new ServletApplicationException("Entry missing in configuration.xml: security.authentication");
		$currentPage = $this->request->getAttribute("page_url");

		if($xml->by_route) {
			//$loggedInFailureCallback = "index", $loggedOutFailureCallback = "login"
			require_once("application/models/XMLAuthorizationWrapper.php");
			new XMLAuthorizationWrapper($this->application->getXML(), $currentPage, $this->persistenceDrivers);
		}
		if($xml->by_dao) {
			require_once("application/models/authentication/Oauth2AuthenticationWrapper.php");
			new Oauth2AuthenticationWrapper($xml->oauth2, $currentPage, $this->persistenceDrivers);
		}
	}
}