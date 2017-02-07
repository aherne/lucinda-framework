<?php
interface UserOauth2AuthenticationDAO {
    function login(UserInformation $userInformation, $createIfNotExists=true);
    function logout($userID);
}