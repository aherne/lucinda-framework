<?php
// generic code
$xmlString = '
		<xml>
			<security>
				<csrf secret="98K[3z66JJiIqV31h-9(" expiration="2"/>
			</security>
		</xml>';
$xml = simplexml_load_string($xmlString);
require_once(str_replace("test/security","src/security", __FILE__));
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/servlets/src/exceptions/ApplicationException.php");
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/security/src/SecurityException.php");
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/security/src/authentication/AuthenticationException.php");
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/security/src/token/SynchronizerToken.php");

// test ok situation
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
$wrapper = new CsrfTokenWrapper($xml->security->csrf);
$token = $wrapper->generate(2);
echo __LINE__.":".($wrapper->isValid($token, 2)?"OK":"FAILED")."\n";

// test security exception situation (token accessed from different ip)
$_SERVER["REMOTE_ADDR"] = "72.229.28.181";
$wrapper = new CsrfTokenWrapper($xml->security->csrf);
echo __LINE__.":".(!$wrapper->isValid($token, 2)?"OK":"FAILED")."\n";

// test security exception situation (timeout expired)
$_SERVER["REMOTE_ADDR"] = "72.229.28.185";
sleep(3);
$wrapper = new CsrfTokenWrapper($xml->security->csrf);
echo __LINE__.":".(!$wrapper->isValid($token, 2)?"OK":"FAILED")."\n";

// test bad token
echo __LINE__.":".(!$wrapper->isValid("aaa", 2)?"OK":"FAILED")."\n";