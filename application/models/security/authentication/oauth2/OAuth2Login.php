<?php
interface OAuth2Login {
	function getAuthorizationCode();
	function login($authorizationCode);
	function getAccessToken();
}