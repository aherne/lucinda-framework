<?php
// generic code
$xmlString = '
		<xml>
			<routes>
				<route url="asd" roles="GUEST"/>
				<route url="fgh" roles="USER"/>
				<route url="jkl" roles="USER1"/>
			</routes>
			<security>
				<authorization>
					<by_xml logged_in_callback="indexz" logged_out_callback="loginz"/>
				</authorization>
			</security>
		</xml>';
$xml = simplexml_load_string($xmlString);
require_once(str_replace("test/security","application/models/security", __FILE__));
require_once(dirname(dirname(__DIR__))."/libraries/php-servlets-api/src/exceptions/ApplicationException.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/authorization/XMLAuthorization.php");

// test non-existent page for guest
$wrapper = new XMLAuthorizationWrapper($xml, "qwe", array("GUEST"));
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::NOT_FOUND && $result->getCallbackURI()=="loginz"?"OK":"FAILED")."\n";

// test non-existent page for logged in user
$wrapper = new XMLAuthorizationWrapper($xml, "qwe", array("USER"));
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::NOT_FOUND && $result->getCallbackURI()=="indexz"?"OK":"FAILED")."\n";

// test existent page allowed for guest
$wrapper = new XMLAuthorizationWrapper($xml, "asd", array("GUEST"));
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::OK?"OK":"FAILED")."\n";

// test existent page not allowed for guest
$wrapper = new XMLAuthorizationWrapper($xml, "fgh", array("GUEST"));
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::UNAUTHORIZED && $result->getCallbackURI()=="loginz"?"OK":"FAILED")."\n";

// test existent page allowed for user
$wrapper = new XMLAuthorizationWrapper($xml, "fgh", array("USER"));
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::OK?"OK":"FAILED")."\n";


// test existent page not allowed for user
$wrapper = new XMLAuthorizationWrapper($xml, "jkl", array("USER"));
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::FORBIDDEN?"OK":"FAILED")."\n";