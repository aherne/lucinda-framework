<?php
interface UserOauth2AuthenticationDAO {
    function login(UserInformation $userInformation, $accessToken, $createIfNotExists=true);
    function logout($userID);
    function getAccessToken($userID);
}