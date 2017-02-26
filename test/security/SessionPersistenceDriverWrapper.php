<?php
// generic code
$xmlString = '
		<xml>
			<security>
				<persistence>
					<session parameter_name="uids"  expiration="1" is_http_only="1" is_https_only="1"/>
				</persistence>
			</security>
		</xml>';
$xml = simplexml_load_string($xmlString);
require_once(str_replace("test/security","application/models/security", __FILE__));
require_once(dirname(dirname(__DIR__))."/libraries/php-servlets-api/src/exceptions/ApplicationException.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/token/SynchronizerToken.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/authentication/SessionPersistenceDriver.php");

// test empty cookie
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new SessionPersistenceDriverWrapper($xml->security->persistence->session);
echo __LINE__.":".($wrapper->getDriver()->load()===null?"OK":"FAILED")."\n";

// test normal situation
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new SessionPersistenceDriverWrapper($xml->security->persistence->session);
$wrapper->getDriver()->save(11);
echo __LINE__.":".($wrapper->getDriver()->load()==11?"OK":"FAILED")."\n";

// test session expired
sleep(2);
$wrapper = new SessionPersistenceDriverWrapper($xml->security->persistence->session);
echo __LINE__.":".($wrapper->getDriver()->load()===null?"OK":"FAILED")."\n";

// test session from a different ip
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new SessionPersistenceDriverWrapper($xml->security->persistence->session);
$wrapper->getDriver()->save(11);
$_SERVER["REMOTE_ADDR"] = "72.229.28.181";
$wrapper = new SessionPersistenceDriverWrapper($xml->security->persistence->session);
$ok = false;
try {
	$wrapper->getDriver()->load();
} catch(SecurityException $e) {
	$ok = true;
}
echo __LINE__.":".($ok?"OK":"FAILED")."\n";