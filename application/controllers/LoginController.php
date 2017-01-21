<?php
require_once("application/controllers/AbstractHtmlController.php");
require_once("application/models/dao/Users.php");
require_once("application/models/Encryption.php");

class LoginController extends AbstractHtmlController {
    protected function service() {
        if(!empty($_POST)) {
            // attempt to login
            $object = new Users();
            $response = $object->login($_POST['email'], $_POST['password']);
            if($response) {
				$_SESSION['user_id'] = $response->id;
				if(!empty($_POST['remember_me'])) {
					$encryption = new Encryption($this->application->getAttribute("remember_me_secret"));
					$this->request->getCookie()->set("user_id", $encryption->encrypt($response->id));
				}                
				Response::sendRedirect("index?status=LOGIN_SUCCESS");
            } else {
                $this->statusCode = "LOGIN_FAILED";
            }
        }
    }
}