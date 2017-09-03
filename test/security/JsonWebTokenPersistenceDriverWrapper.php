<?php
// generic code
$xmlString = '
		<xml>
			<security>
				<persistence>
					<json_web_token secret="98K[3z66JJiIqV31h-9(" expiration="3" regeneration="1"/>
				</persistence>
			</security>
		</xml>';
$xml = simplexml_load_string($xmlString);
require_once(str_replace("test/security","src/security", __FILE__));
require_once(dirname(dirname(__DIR__))."/libraries/php-servlets-api/src/exceptions/ApplicationException.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/HackingException.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/authentication/AuthenticationException.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/token/JsonWebToken.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/authentication/JsonWebTokenPersistenceDriver.php");

// test empty cookie
$wrapper = new JsonWebTokenPersistenceDriverWrapper($xml->security->persistence->json_web_token);
echo __LINE__.":".($wrapper->getDriver()->load()===null?"OK":"FAILED")."\n";

// test normal situation
$wrapper = new JsonWebTokenPersistenceDriverWrapper($xml->security->persistence->json_web_token);
$wrapper->getDriver()->save(11);
$token = $wrapper->getDriver()->getAccessToken();
echo __LINE__.":".($token?"OK":"FAILED")."\n";
$_SERVER["HTTP_AUTHORIZATION"] = "Bearer ".$token;
echo __LINE__.":".($wrapper->getDriver()->load()==11?"OK":"FAILED")."\n";

// test regeneration
sleep(2);
echo __LINE__.":".($wrapper->getDriver()->load()==11 && $wrapper->getDriver()->getAccessToken()!=$token?"OK":"FAILED")."\n";
$newToken = $wrapper->getDriver()->getAccessToken();

// test expiration
sleep(4);
$wrapper = new JsonWebTokenPersistenceDriverWrapper($xml->security->persistence->json_web_token);
$_SERVER["HTTP_AUTHORIZATION"] = "Bearer ".$newToken;
echo __LINE__.":".($wrapper->getDriver()->load()===null?"OK":"FAILED")."\n";
