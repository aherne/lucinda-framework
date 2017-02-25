<?php
// generic code
$xmlString = '
		<xml>
			<routes>
				<route url="asd" access="ROLE_GUEST"/>
				<route url="fgh" access="ROLE_USER"/>
			</routes>
			<security>
				<authorization>
					<by_route logged_in_callback="indexz" logged_out_callback="loginz"/>
				</authorization>
			</security>
		</xml>';
$xml = simplexml_load_string($xmlString);
require_once(str_replace("test/security","application/models/security", __FILE__));
require_once(dirname(dirname(__DIR__))."/libraries/php-servlets-api/src/exceptions/ApplicationException.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/authorization/XMLAuthorization.php");

// test non-existent page for guest
$wrapper = new XMLAuthorizationWrapper($xml, "qwe", null);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::NOT_FOUND && $result->getCallbackURI()=="loginz"?"OK":"FAILED")."\n";

// test non-existent page for logged in user
$wrapper = new XMLAuthorizationWrapper($xml, "qwe", 1);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::NOT_FOUND && $result->getCallbackURI()=="indexz"?"OK":"FAILED")."\n";

// test existent page allowed for guest
$wrapper = new XMLAuthorizationWrapper($xml, "asd", null);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::OK?"OK":"FAILED")."\n";

// test existent page not allowed for guest
$wrapper = new XMLAuthorizationWrapper($xml, "fgh", null);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::UNAUTHORIZED && $result->getCallbackURI()=="loginz"?"OK":"FAILED")."\n";

// test existent page allowed for user
$wrapper = new XMLAuthorizationWrapper($xml, "fgh", 1);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::OK?"OK":"FAILED")."\n";