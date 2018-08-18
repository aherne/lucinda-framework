<?php
class LoginController extends Controller
{
    public function run() {
        if($this->request->getCookie()->contains("remember_me") || $this->request->getSession()->contains("user_id")) {
            $this->response->sendRedirect("/");
        }
        $this->response->setAttribute("csrf", $this->request->getAttribute("csrf")->generate(0));
        $this->response->setView("test/views/login");
    }
}

