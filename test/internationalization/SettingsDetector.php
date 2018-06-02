<?php
require_once(str_replace("/test/","/src/",__FILE__));
require_once(dirname(__DIR__)."/request.php");
require_once(dirname(dirname(__DIR__))."/src/internationalization/LocaleDetector.php");
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/internationalization/src/Settings.php");

$xml = '<internationalization locale="en_US" method="header" folder="locale" domain="messages"/>';
$localeDetector = new LocaleDetector(simplexml_load_string($xml), new Request());

$settingsDetector = new SettingsDetector("", simplexml_load_string($xml), $localeDetector);
echo __LINE__.": ".($settingsDetector->getSettings()->getCharset()=="UTF-8"?"Y":"N")."\n";
echo __LINE__.": ".($settingsDetector->getSettings()->getDomain()=="messages"?"Y":"N")."\n";
echo __LINE__.": ".($settingsDetector->getSettings()->getFolder()==="locale"?"Y":"N")."\n";
echo __LINE__.": ".($settingsDetector->getSettings()->getLocale()==="en_US"?"Y":"N")."\n";