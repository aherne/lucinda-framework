<?php
require_once("libraries/php-security-api/src/Encryption.php");

/**
 * Implements a basic authorization using sessions & cookies to keep state between requests and database as provider
 */
class Authorization extends RequestListener {
	public function run() {			
		// starts session
		$this->request->getSession()->start();

		// authenticate
		$authorization = new BasicAuthorization($this->getUserId(),$this->request->getAttribute("page_url"));
		if($authorization->getStatus()!=BasicAuthorization::STATUS_OK) {
			if($this->request->getAttribute("page_extension")=="html") {
				Response::sendRedirect("/".$authorization->getPage()."?status=".$authorization->getStatus());
			} else {
				throw new Exception("You are not authorized to perform this operation!");
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
			$encryption = new Encryption($this->application->getAttribute("secret_key"));
			$user_id = $encryption->decrypt($this->request->getCookie()->get("user_id"));
			$this->request->getSession()->set("user_id", $user_id); // restores session user id
			return $user_id;
		}

		return 0;
	}
}