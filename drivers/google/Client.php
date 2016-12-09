<?php
namespace Google;

require_once(dirname(dirname(__DIR__))."/loader.php");
require_once("ResponseWrapper.php");

use OAuth2\ClientInformation as OAuth2_ClientInformation;
use OAuth2\AccessTokenRequest as OAuth2_AccessTokenRequest;
use OAuth2\AccessTokenResponse as OAuth2_AccessTokenResponse;
use OAuth2\AuthorizationCodeRequest as OAuth2_AuthorizationCodeRequest;
use OAuth2\WrappedExecutor as OAuth2_WrappedExecutor;
use OAuth2\HttpMethod as OAuth2_HttpMethod;
use OAuth2\RedirectionExecutor as OAuth2_RedirectionExecutor;

class Client {
	private $clientInformation;

	public function __construct(OAuth2_ClientInformation $clientInformation) {
		$this->clientInformation = $clientInformation;
	}

	public function getAuthorizationCode($scopes) {
		$acr = new OAuth2_AuthorizationCodeRequest("https://accounts.google.com/o/oauth2/auth");
		$acr->setClientInformation($this->clientInformation);
		$acr->setRedirectURL($this->clientInformation->getSiteURL());
		$acr->setScope(implode(",",$scopes));
		$acr->execute(new OAuth2_RedirectionExecutor());
	}

	public function getAccessToken($authorizationCode) {
		$atr = new OAuth2_AccessTokenRequest("https://accounts.google.com/o/oauth2/token");
		$atr->setClientInformation($this->clientInformation);
		$atr->setCode($authorizationCode);
		$atr->setRedirectURL($this->clientInformation->getSiteURL());
		$acrw = new ResponseWrapper();
		$atr->execute(new OAuth2_WrappedExecutor($acrw));
		return new OAuth2_AccessTokenResponse($acrw->getResponse());
	}

	public function getResource($accessToken, $service, $fields=array()) {
		$rrw = new ResponseWrapper();
		$we = new OAuth2_WrappedExecutor($rrw);
		$we->setHttpMethod(OAuth2_HttpMethod::GET);
		$we->addAuthorizationToken("Bearer",$accessToken);
		$parameters = (!empty($fields)?array("fields"=>implode(",",$fields)):array());
		$we->execute("https://www.googleapis.com/plus/v1/".$service, $parameters);
		return $rrw->getResponse();
	}
}