<?php
require_once(str_replace("/test/","/src/",__FILE__));
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/security/loader.php");
require_once(dirname(dirname(__DIR__))."/src/security/PersistenceDriversDetector.php");

// create test environment
$xml = '
<xml>
    <security>
        <persistence>
            <synchronizer_token secret="rrwe"/>
        </persistence>
    </security>
</xml>
';

// csrf
$userID = 1;
$_SERVER = array("HTTP_X_FORWARDED_FOR"=>"82.76.206.3","REMOTE_ADDR"=>"192.168.21.211");
$pdd = new PersistenceDriversDetector(simplexml_load_string($xml));
$persistenceDriver = $pdd->getPersistenceDrivers()[0];
$persistenceDriver->save($userID);


$uid = new UserIdDetector($pdd->getPersistenceDrivers());
echo __LINE__.": ".($uid->getUserID()==1?"Y":"N")."\n";