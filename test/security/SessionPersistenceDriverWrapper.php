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
require_once(str_replace("test/security","src/security", __FILE__));
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/mvc/src/exceptions/ApplicationException.php");
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/security/src/SecurityException.php");
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/security/src/authentication/AuthenticationException.php");
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/security/src/token/SynchronizerToken.php");
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/security/src/authentication/SessionPersistenceDriver.php");

$output = array();
// test empty cookie
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new SessionPersistenceDriverWrapper($xml->security->persistence->session);
$output[]=__LINE__.":".($wrapper->getDriver()->load()===null?"OK":"FAILED")."\n";

// test normal situation
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new SessionPersistenceDriverWrapper($xml->security->persistence->session);
$wrapper->getDriver()->save(11);
$output[]=__LINE__.":".($wrapper->getDriver()->load()==11?"OK":"FAILED")."\n";

// test session expired
sleep(2);
$wrapper = new SessionPersistenceDriverWrapper($xml->security->persistence->session);
$output[]=__LINE__.":".($wrapper->getDriver()->load()===null?"OK":"FAILED")."\n";

// test session from a different ip
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new SessionPersistenceDriverWrapper($xml->security->persistence->session);
$wrapper->getDriver()->save(11);
$_SERVER["REMOTE_ADDR"] = "72.229.28.181";
$wrapper = new SessionPersistenceDriverWrapper($xml->security->persistence->session);
$ok = false;
try {
	$wrapper->getDriver()->load();
} catch(SessionHijackException $e) {
	$ok = true;
}
$output[] = __LINE__.":".($ok?"OK":"FAILED")."\n";


foreach($output as $line) {
	echo $line;
}