<?php
require_once(str_replace("/test/","/src/",__FILE__));
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/security/loader.php");
require_once(dirname(dirname(__DIR__))."/src/security/CsrfTokenDetector.php");
require_once(dirname(dirname(__DIR__))."/src/security/PersistenceDriversDetector.php");
require_once(dirname(dirname(__DIR__))."/src/security/SecurityPacket.php");
require_once(dirname(__DIR__)."/application.php");
require_once(dirname(__DIR__)."/request.php");

function getRequestObject($page, $csrf, $application) {
    $_SERVER["REQUEST_URI"] = "/".$page;
    $request = new Request();
    $request->setValidator(new PageValidator($page, $application));
    $request->setAttribute("csrf", $csrf);
    return $request;
}

$application = new Application("configuration.xml");

$csrf = new CsrfTokenDetector($application->getXML());

$pdd = new PersistenceDriversDetector($application->getXML());

// no authentication requested
new Authentication($application->getXML(), getRequestObject("login", $csrf, $application), $pdd->getPersistenceDrivers());
echo __LINE__.": Y\n";

// login failed
$_POST = array("username"=>"lucian", "password"=>"epopescu", "csrf" => $csrf->generate(0));
try {
    new Authentication($application->getXML(), getRequestObject("login", $csrf, $application), $pdd->getPersistenceDrivers());
    echo __LINE__.": N\n";
} catch(SecurityPacket $e) {
    echo __LINE__.": ".($e->getStatus() == "login_failed"?"Y":"N")."\n";
}

// login success
$_POST = array("username"=>"lucian", "password"=>"popescu", "csrf" => $csrf->generate(0));
try {
    new Authentication($application->getXML(), getRequestObject("login", $csrf, $application), $pdd->getPersistenceDrivers());
    echo __LINE__.": N\n";
} catch(SecurityPacket $e) {
    echo __LINE__.": ".($e->getStatus() == "login_ok"?"Y":"N")."\n";
}

// logout success
try {
    new Authentication($application->getXML(), getRequestObject("logout", $csrf, $application), $pdd->getPersistenceDrivers());
} catch(SecurityPacket $e) {
    echo __LINE__.": ".($e->getStatus() == "logout_ok"?"Y":"N")."\n";
}

// logout failed
try {
    new Authentication($application->getXML(), getRequestObject("logout", $csrf, $application), $pdd->getPersistenceDrivers());
} catch(SecurityPacket $e) {
    echo __LINE__.": ".($e->getStatus() == "logout_failed"?"Y":"N")."\n";
}