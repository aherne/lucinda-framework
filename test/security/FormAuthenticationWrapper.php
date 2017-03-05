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
					<form dao="MockUserAuthenticationDAO">
						<login parameter_username="x" parameter_password="y"  parameter_rememberMe="z" page="loginz" target="indexz"/>
						<logout page="logoutz" target="loginz"/>
					</form>
				</authentication>
				<csrf secret="98K[3z66JJiIqV31h-9(" expiration="2"/>
			</security>
		</xml>';
$xml = simplexml_load_string($xmlString);
require_once(str_replace("test/security","src/security", __FILE__));
require_once(dirname(dirname(__DIR__))."/libraries/php-servlets-api/src/exceptions/ApplicationException.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/authentication/FormAuthentication.php");
require_once(dirname(dirname(__DIR__))."/src/security/DAOLocator.php");
require_once(dirname(dirname(__DIR__))."/src/security/CsrfTokenWrapper.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-servlets-api/src/exceptions/ApplicationException.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/token/SynchronizerToken.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/authentication/PersistenceDriver.php");
require_once("mocks/MockPersistenceDriver.php");

// instance csrf token
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$csrf = new CsrfTokenWrapper($xml->security->csrf);
$token = $csrf->generate(0);

// instance persistence drivers
$pd1 = new MockPersistenceDriver();
$pd2 = new MockPersistenceDriver();

// test page other than login / logout
$wrapper = new FormAuthenticationWrapper($xml,"xasd", array(), $csrf);
$result = $wrapper->getResult();
echo __LINE__.":".($result===null?"OK":"FAILED")."\n";

// test login
$_POST = array("x"=>"asd","y"=>"fgh","csrf"=>$token);
$wrapper = new FormAuthenticationWrapper($xml,"loginz", array($pd1,$pd2), $csrf);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthenticationResultStatus::OK && $result->getCallbackURI()=="indexz"?"OK":"FAILED")."\n";
// test values have been persisted
echo __LINE__.":".($result->getUserID()==$pd1->load() && $result->getUserID()==$pd2->load()?"OK":"FAILED")."\n";

// test logout
$wrapper = new FormAuthenticationWrapper($xml,"logoutz", array($pd1,$pd2), $csrf);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthenticationResultStatus::OK && $result->getCallbackURI()=="loginz"?"OK":"FAILED")."\n";
// test values have been persisted
echo __LINE__.":".($pd1->load()===null && $pd2->load()===null?"OK":"FAILED")."\n";

// test login that fails due to missing csrf token
$ok = false;
try {
	$_POST = array("x"=>"asd","y"=>"fgh");
	$wrapper = new FormAuthenticationWrapper($xml,"loginz", array($pd1,$pd2), $csrf);
} catch(TokenException $e) {
	$ok=true;
}
echo __LINE__.":".($ok?"OK":"FAILED")."\n";

// test login that fails due to missing post parameters
$ok = false;
try {
	$_POST = array("x"=>"asd","m"=>"fgh","csrf"=>$token);
	$wrapper = new FormAuthenticationWrapper($xml,"loginz", array($pd1,$pd2), $csrf);
} catch(AuthenticationException $e) {
	$ok=true;
}
echo __LINE__.":".($ok?"OK":"FAILED")."\n";

// test login that fails due to bad credentials
$_POST = array("x"=>"asd","y"=>"ert","csrf"=>$token);
$wrapper = new FormAuthenticationWrapper($xml,"loginz", array($pd1,$pd2), $csrf);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthenticationResultStatus::LOGIN_FAILED && $result->getCallbackURI()=="loginz"?"OK":"FAILED")."\n";

// test logout that fails due to missing user id
$wrapper = new FormAuthenticationWrapper($xml,"logoutz", array($pd1,$pd2), $csrf);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthenticationResultStatus::LOGOUT_FAILED?"OK":"FAILED")."\n";
