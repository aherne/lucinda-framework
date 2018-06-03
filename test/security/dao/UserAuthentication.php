<?php
class UserAuthentication implements UserAuthenticationDAO {
    public function logout($userID)
    {}

    public function login($username, $password, $rememberMe = null)
    {
        return ($username=="lucian" && $password=="popescu"?1:0);
    }
}