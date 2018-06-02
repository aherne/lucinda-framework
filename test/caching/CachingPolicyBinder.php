<?php
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/http-caching/loader.php");
require_once(str_replace("/test/","/src/",__FILE__));

require_once(dirname(__DIR__)."/application.php");
require_once(dirname(__DIR__)."/request.php");

// instance application object
$application = new Application("configuration.xml");

// check global caching
echo "GLOBAL SETTINGS:\n";
$request = new Request();
$request->setValidator(new PageValidator("index", $application));
$test = new CachingPolicyBinder($application, $request);
echo "getCachingDisabled: ".($test->getPolicy()->getCachingDisabled()?"NOK":"OK")."\n";
echo "getExpirationPeriod: ".($test->getPolicy()->getExpirationPeriod()==100?"OK":"NOK")."\n";
echo "getCacheableDriver: ".(get_class($test->getPolicy()->getCacheableDriver())=="TestCacheable"?"OK":"NOK")."\n";

// check route-based caching
echo "ROUTE-BASED SETTINGS:\n";
$_SERVER["REQUEST_URI"] = "/login";
$request = new Request();
$request->setValidator(new PageValidator("login", $application));
$test = new CachingPolicyBinder($application, $request);
echo "getCachingDisabled: ".($test->getPolicy()->getCachingDisabled()?"OK":"NOK")."\n";
echo "getExpirationPeriod: ".($test->getPolicy()->getExpirationPeriod()==100?"OK":"NOK")."\n";
echo "getCacheableDriver: ".(get_class($test->getPolicy()->getCacheableDriver())=="TestCacheable"?"OK":"NOK");