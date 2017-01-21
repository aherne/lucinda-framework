<?php
require_once("application/models/BasicAuthorization.php");
require_once("application/models/Encryption.php");

class Authorization extends RequestListener {
	public function run() {
		// do not answer to favicon.ico requests
		if(!empty($_SERVER["REDIRECT_URL"]) && strpos($_SERVER["REDIRECT_URL"], "favicon.ico")!==false) {
			header("HTTP/1.0 404 Not Found");
			die();
		}
			
		// starts sessions
		$this->request->getSession()->start();

		// determine user id
		$encryption = new Encryption($this->application->getAttribute("remember_me_secret"));

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
			$encryption = new Encryption($this->application->getAttribute("remember_me_secret"));
			$user_id = $encryption->decrypt($this->request->getCookie()->get("user_id"));
			$_SESSION["user_id"] = $user_id; // restore user id session
			return $user_id;
		}

		return 0;
	}
}