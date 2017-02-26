<?php
// generic code
$xmlString = '
		<xml>
			<security>
				<persistence>
					<remember_me secret="98K[3z66JJiIqV31h-9(" parameter_name="uids"  expiration="1" is_http_only="1" is_https_only="1"/>
				</persistence>
			</security>
		</xml>';
$xml = simplexml_load_string($xmlString);
require_once(str_replace("test/security","src/security", __FILE__));
require_once(dirname(dirname(__DIR__))."/libraries/php-servlets-api/src/exceptions/ApplicationException.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/token/SynchronizerToken.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/authentication/RememberMePersistenceDriver.php");

// test empty cookie
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new RememberMePersistenceDriverWrapper($xml->security->persistence->remember_me);
echo __LINE__.":".($wrapper->getDriver()->load()===null?"OK":"FAILED")."\n";

// test normal situation
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new RememberMePersistenceDriverWrapper($xml->security->persistence->remember_me);
$wrapper->getDriver()->save(11);
echo __LINE__.":".($wrapper->getDriver()->load()==11?"OK":"FAILED")."\n";

// test remember me expired
sleep(2);
$wrapper = new RememberMePersistenceDriverWrapper($xml->security->persistence->remember_me);
echo __LINE__.":".($wrapper->getDriver()->load()===null?"OK":"FAILED")."\n";

// test remember me from a different ip
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new RememberMePersistenceDriverWrapper($xml->security->persistence->remember_me);
$wrapper->getDriver()->save(11);
$_SERVER["REMOTE_ADDR"] = "72.229.28.181";
$wrapper = new RememberMePersistenceDriverWrapper($xml->security->persistence->remember_me);
$ok = false;
try {
	$wrapper->getDriver()->load();
} catch(TokenException $e) {
	$ok = true;
}
echo __LINE__.":".($ok?"OK":"FAILED")."\n";