<?php
// generic code
$xmlString = '
		<xml>
			<application>
				<paths>
					<dao>mocks</dao>
				</paths>
			</application>
			<security>
				<authentication>
					<oauth2 dao="MockOAuth2AuthenticationDAO" target="indexz" login="loginz" logout="logoutz" auto_create="1">
						<driver name="mock" callback="callbackz">
							<client_id>a</client_id>
							<client_secret>b</client_secret>
							<scopes>m,u</scopes>
						</driver>
					</oauth2>
				</authentication>
				<csrf secret="98K[3z66JJiIqV31h-9(" expiration="2"/>
			</security>
		</xml>';
$xml = simplexml_load_string($xmlString);
require_once(str_replace("test/security","application/models/security", __FILE__));
require_once(dirname(dirname(__DIR__))."/libraries/php-servlets-api/src/exceptions/ApplicationException.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/authentication/OAuth2Authentication.php");
require_once(dirname(dirname(__DIR__))."/application/models/security/DAOLocator.php");
require_once(dirname(dirname(__DIR__))."/application/models/security/CsrfTokenWrapper.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/token/SynchronizerToken.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/authentication/PersistenceDriver.php");
require_once(dirname(dirname(__DIR__))."/libraries/oauth2client/loader.php");
require_once("mocks/MockPersistenceDriver.php");

// instance persistence drivers
$pd1 = new MockPersistenceDriver();
$pd2 = new MockPersistenceDriver();

// instance csrf token
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$csrf = new CsrfTokenWrapper($xml->security->csrf);
$token = $csrf->generate(0);

// test page other than login / logout
$wrapper = new Oauth2AuthenticationWrapper($xml, "aaa", array($pd1, $pd2), $csrf);
$result = $wrapper->getResult();
echo __LINE__.":".($result===null?"OK":"FAILED")."\n";

// test authorization code retrieval
$_SERVER=array("HTTPS"=>"https","HTTP_HOST"=>"www.test.com","QUERY_STRING"=>"","REQUEST_URI"=>"/mock/login");
$wrapper = new Oauth2AuthenticationWrapper($xml, "callbackz", array($pd1, $pd2), $csrf);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthenticationResultStatus::DEFERRED && strpos($result->getCallbackURI(),"https://www.test.com?response_type=code&client_id=a&redirect_uri=https%3A%2F%2Fwww.test.com%2Fmock%2Flogin&scope=m+u&state=")!==false?"OK":"FAILED")."\n";

// test login
$_GET=array("code"=>"qwerty","state"=>$token);
$wrapper = new Oauth2AuthenticationWrapper($xml, "callbackz", array($pd1, $pd2), $csrf);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthenticationResultStatus::OK && $result->getUserID()==11 && $result->getAccessToken()=="xyz" && $result->getCallbackURI()=="indexz"?"OK":"FAILED")."\n";
// test values have been persisted
echo __LINE__.":".($result->getUserID()==$pd1->load() && $result->getUserID()==$pd2->load()?"OK":"FAILED")."\n";

// test error in getting access token
$_GET=array("code"=>"qwertyz","state"=>$token);
$ok = false;
try {
	$wrapper = new Oauth2AuthenticationWrapper($xml, "callbackz", array($pd1, $pd2), $csrf);
} catch(OAuth2\ServerException $se) {
	$ok = true;
}
echo __LINE__.":".($ok?"OK":"FAILED")."\n";

// test error in getting resource response
$_GET=array("code"=>"qwertyt","state"=>$token);
$ok = false;
try {
	$wrapper = new Oauth2AuthenticationWrapper($xml, "callbackz", array($pd1, $pd2), $csrf);
} catch(OAuth2\ServerException $se) {
	$ok = true;
}
echo __LINE__.":".($ok?"OK":"FAILED")."\n";

// test logout
$wrapper = new Oauth2AuthenticationWrapper($xml, "logoutz", array($pd1, $pd2), $csrf);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthenticationResultStatus::OK && $result->getCallbackURI()=="loginz"?"OK":"FAILED")."\n";
// test values have been persisted
echo __LINE__.":".($pd1->load()===null && $pd2->load()===null?"OK":"FAILED")."\n";

// test logout
$wrapper = new Oauth2AuthenticationWrapper($xml, "logoutz", array($pd1, $pd2), $csrf);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthenticationResultStatus::LOGOUT_FAILED && $result->getCallbackURI()=="loginz"?"OK":"FAILED")."\n";