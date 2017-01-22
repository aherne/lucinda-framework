<?php
require_once("libraries/php-security-api/src/Encryption.php");
require_once("libraries/php-security-api/src/SecurityException.php");
require_once("libraries/php-security-api/src/authorization/Authorization.php");

/**
 * Implements a basic authorization using sessions & cookies to keep state between requests and database as provider

no authorization
logged in user can access any site resource
logged in user must be allowed to access protected page

state is not preserved between requests
state is preserved through sessions/cookies
state is preserved through json/synchronizer tokens
 */
class SessionAuthorization extends RequestListener {
	public function run() {			
		// starts session
		$this->request->getSession()->start();
		
		// determine default landing pages from XML
		$loggedInHome = (string) $this->application->getXML()->application->home_loggedin;
		$loggedOutHome = (string) $this->application->getXML()->application->home_loggedout;
		
		// authorize
		$authorization = new Authorization($loggedInHome, $loggedOutHome);
		$results = $authorization->authorize(new UserDetails($this->getUserId()), new PageDetails($this->request->getAttribute("page_url")));
		if($results->getStatus()!=AuthorizationResult::STATUS_OK) {
			if($this->request->getAttribute("page_extension")=="html") {
				Response::sendRedirect("/".$results->getCallbackURI()."?status=".$authorization->getStatus());
			} else {
				throw new SecurityException("You are not authorized to perform this operation!");
			}
		}
	}

	/**
	 * Gets user id from session or cookie.
	 *
	 * @return integer
	 */
	private function getUserId() {
		// return from session if found
		if($this->request->getSession()->contains("user_id")) {
			return $this->request->getSession()->get("user_id");
		}

		// return from cookie if not found @ session
		if($this->request->getCookie()->contains("user_id")) {
			// get secret key from xml
			$secretKey = (string) $this->application->getXML()->application->secret_key;
			if(!$secretKey) throw new ServletException("Decryption requires a secret key!");
			// perform decryption to get user_id
			$encryption = new Encryption($secretKey);
			$user_id = $encryption->decrypt($this->request->getCookie()->get("user_id"));
			if(!$user_id) throw new ServletException("User not found!");
			$this->request->getSession()->set("user_id", $user_id); // restores session user id
			return $user_id;
		}

		return 0;
	}
}