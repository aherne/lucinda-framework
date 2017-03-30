<?php
// generic code
$xmlString = '
		<xml>
			<security>
				<persistence>
					<token secret="98K[3z66JJiIqV31h-9(" expiration="3" regeneration="1"/>
				</persistence>
			</security>
		</xml>';
$xml = simplexml_load_string($xmlString);
require_once(str_replace("test/security","application/models/security", __FILE__));
require_once(dirname(dirname(__DIR__))."/libraries/php-servlets-api/src/exceptions/ApplicationException.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/token/SynchronizerToken.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/authentication/TokenPersistenceDriver.php");

// test empty cookie
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new TokenPersistenceDriverWrapper($xml->security->persistence->token);
echo __LINE__.":".($wrapper->getDriver()->load()===null?"OK":"FAILED")."\n";

// test normal situation
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new TokenPersistenceDriverWrapper($xml->security->persistence->token);
$wrapper->getDriver()->save(11);
$token = $wrapper->getDriver()->getAccessToken();
echo __LINE__.":".($token?"OK":"FAILED")."\n";
$_SERVER["HTTP_AUTHORIZATION"] = "Token ".$token;
echo __LINE__.":".($wrapper->getDriver()->load()==11?"OK":"FAILED")."\n";

// test regeneration
sleep(2);
echo __LINE__.":".($wrapper->getDriver()->load()==11 && $wrapper->getDriver()->getAccessToken()!=$token?"OK":"FAILED")."\n";
$newToken = $wrapper->getDriver()->getAccessToken();

// test token from a different ip
$_SERVER["REMOTE_ADDR"] = "72.229.28.181";
$wrapper = new TokenPersistenceDriverWrapper($xml->security->persistence->token);
$ok = false;
try {
	$_SERVER["HTTP_AUTHORIZATION"] = "Token ".$newToken;
	$wrapper->getDriver()->load();
} catch(TokenException $e) {
	$ok = true;
}
echo __LINE__.":".($ok?"OK":"FAILED")."\n";

// test expiration
sleep(4);
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new TokenPersistenceDriverWrapper($xml->security->persistence->token);
$_SERVER["HTTP_AUTHORIZATION"] = "Token ".$newToken;
echo __LINE__.":".($wrapper->getDriver()->load()===null?"OK":"FAILED")."\n";
