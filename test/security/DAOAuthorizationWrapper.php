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
				<authorization>
					<by_dao logged_in_callback="indexz" logged_out_callback="loginz" page_dao="MockPageDAO" user_dao="MockUserAuthorizationDAO"/>
				</authorization>
			</security>
		</xml>';
$xml = simplexml_load_string($xmlString);
require_once(str_replace("test/security","src/security", __FILE__));
require_once(dirname(dirname(__DIR__))."/libraries/php-servlets-api/src/exceptions/ApplicationException.php");
require_once(dirname(dirname(__DIR__))."/libraries/php-security-api/src/authorization/DAOAuthorization.php");
require_once(dirname(dirname(__DIR__))."/src/security/DAOLocator.php");

$locator = new DAOLocator($xml);

// test non-existent page for guest
$wrapper = new DAOAuthorizationWrapper($xml->security->authorization->by_dao, "qwe", null, $locator);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::NOT_FOUND && $result->getCallbackURI()=="loginz"?"OK":"FAILED")."\n";

// test non-existent page for logged in user
$wrapper = new DAOAuthorizationWrapper($xml->security->authorization->by_dao, "qwe", 1, $locator);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::NOT_FOUND && $result->getCallbackURI()=="indexz"?"OK":"FAILED")."\n";

// test existent page allowed for guest
$wrapper = new DAOAuthorizationWrapper($xml->security->authorization->by_dao, "asd", null, $locator);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::OK?"OK":"FAILED")."\n";

// test existent page not allowed for guest
$wrapper = new DAOAuthorizationWrapper($xml->security->authorization->by_dao, "fgh", null, $locator);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::UNAUTHORIZED && $result->getCallbackURI()=="loginz"?"OK":"FAILED")."\n";

// test existent page allowed for user
$wrapper = new DAOAuthorizationWrapper($xml->security->authorization->by_dao, "fgh", 1, $locator);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::OK?"OK":"FAILED")."\n";

// test existent page not allowed for user
$wrapper = new DAOAuthorizationWrapper($xml->security->authorization->by_dao, "jkl", 1, $locator);
$result = $wrapper->getResult();
echo __LINE__.":".($result->getStatus()==AuthorizationResultStatus::FORBIDDEN && $result->getCallbackURI()=="indexz"?"OK":"FAILED")."\n";