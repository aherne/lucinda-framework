<?php
require_once(str_replace("/test/","/src/",__FILE__));
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/security/loader.php");
require_once(dirname(dirname(__DIR__))."/src/security/SecurityPacket.php");
require_once(dirname(__DIR__)."/application.php");
require_once(dirname(__DIR__)."/request.php");

function getRequestObject($page, $application, $userID) {
    $_SERVER["REQUEST_URI"] = "/".$page;
    $request = new Request();
    $request->setValidator(new PageValidator($page, $application));
    $request->setAttribute("user_id", $userID);
    return $request;
}

$application = new Application("configuration.xml");

// authorization success
new Authorization($application->getXML(), getRequestObject("index", $application, 1));
echo __LINE__.": Y\n";

// authorization failed due to user not logged in
try {
    new Authorization($application->getXML(), getRequestObject("index", $application, 0));
} catch(SecurityPacket $e) {
    echo __LINE__.": ".($e->getStatus() == "unauthorized"?"Y":"N")."\n";
}

// authorization failed due to logged in user not having rights to access page 
try {
    new Authorization($application->getXML(), getRequestObject("private", $application, 1));
} catch(SecurityPacket $e) {
    echo __LINE__.": ".($e->getStatus() == "forbidden"?"Y":"N")."\n";
}

// authorization failed due to page not found in db
try {
    new Authorization($application->getXML(), getRequestObject("missing", $application, 0));
} catch(SecurityPacket $e) {
    echo __LINE__.": ".($e->getStatus() == "not_found"?"Y":"N")."\n";
}