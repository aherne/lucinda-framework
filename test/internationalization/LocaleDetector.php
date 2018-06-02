<?php
require_once(str_replace("/test/","/src/",__FILE__));
require_once(dirname(__DIR__)."/request.php");

$xml = '
<internationalization locale="en_US" method="session">
     <session expiration="1"/>
</internationalization>
';
$_GET["locale"] = "fr_FR";
$localeDetector = new LocaleDetector(simplexml_load_string($xml), new Request());
echo __LINE__.": ".($localeDetector->getDefaultLocale()=="en_US"?"Y":"N")."\n";
echo __LINE__.": ".($localeDetector->getDetectedLocale()=="fr_FR"?"Y":"N")."\n";
echo __LINE__.": ".($localeDetector->getDetectionMethod()==="session"?"Y":"N")."\n";
$_SESSION["locale"] = "fr_FR";

unset($_GET["locale"]);
$localeDetector = new LocaleDetector(simplexml_load_string($xml), new Request());
echo __LINE__.": ".($localeDetector->getDefaultLocale()=="en_US"?"Y":"N")."\n";
echo __LINE__.": ".($localeDetector->getDetectedLocale()=="fr_FR"?"Y":"N")."\n";
echo __LINE__.": ".($localeDetector->getDetectionMethod()==="session"?"Y":"N")."\n";

// <internationalization locale="{value}" domain="{value}" folder="{value}" method="{value}"/>
$xml = '<internationalization locale="en_US" method="header"/>';
$localeDetector = new LocaleDetector(simplexml_load_string($xml), new Request());
echo __LINE__.": ".($localeDetector->getDefaultLocale()=="en_US"?"Y":"N")."\n";
echo __LINE__.": ".($localeDetector->getDetectedLocale()=="en_US"?"Y":"N")."\n";
echo __LINE__.": ".($localeDetector->getDetectionMethod()==="header"?"Y":"N")."\n";

$_SERVER["HTTP_ACCEPT_LANGUAGE"] = "fr-fr,en;q=0.5";
$xml = '<internationalization locale="en_US" method="header"/>';
$localeDetector = new LocaleDetector(simplexml_load_string($xml), new Request());
echo __LINE__.": ".($localeDetector->getDefaultLocale()=="en_US"?"Y":"N")."\n";
echo __LINE__.": ".($localeDetector->getDetectedLocale()=="fr_FR"?"Y":"N")."\n";
echo __LINE__.": ".($localeDetector->getDetectionMethod()==="header"?"Y":"N")."\n";

$_GET["locale"] = "fr_FR";
$xml = '<internationalization locale="en_US" method="request"/>';
$localeDetector = new LocaleDetector(simplexml_load_string($xml), new Request());
echo __LINE__.": ".($localeDetector->getDefaultLocale()=="en_US"?"Y":"N")."\n";
echo __LINE__.": ".($localeDetector->getDetectedLocale()=="fr_FR"?"Y":"N")."\n";
echo __LINE__.": ".($localeDetector->getDetectionMethod()==="request"?"Y":"N")."\n";
