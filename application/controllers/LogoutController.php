<?php
class LogoutController extends Controller {
    public function run() {
        $this->request->getSession()->remove("user_id");
        $this->request->getCookie()->remove("user_id");
        Response::sendRedirect("login?status=LOGOUT_OK");
    }
}