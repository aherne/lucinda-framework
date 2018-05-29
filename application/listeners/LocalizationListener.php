<?php
require_once("vendor/lucinda/internationalization/src/Reader.php");
require_once("src/internationalization/LocaleDetector.php");
require_once("src/internationalization/SettingsDetector.php");

/**
 * Performs internationalization & localization by binding php-internationalization-api with XML tag:
 * <internationalization locale="LOCALE" domain="DOMAIN" folder="FOLDER" method="METHOD"/>
 * or:
 * <internationalization locale="LOCALE" domain="DOMAIN" folder="FOLDER" method="session">
 *  <session expiration="{value}" is_http_only="{value}" is_https_only="{value}" handler="{value}"/>
 * </internationalization>
 *
 * Where:
 * - LOCALE: (mandatory) value of default locale (eg: en_US)
 * - DOMAIN: (optional) name of MO file storing localized content (if not set defaults to: "messages")
 * - FOLDER: (optional) folder storing locales (if not set defaults to "locale")
 * - METHOD: (mandatory) method to be used in detecting locales. Possible values:
 *      - header: detects locale via "Accept-Language" HTTP header
 *      - request: detects locale via "locale" querystring parameter.
 *      - session: detects locale via "locale" session parameter, itself originating from a supported "locale" querystring parameter.
 *
 * If detected locale is not yet supported, uses "en_US". If latter is not supported either, a LocaleException is thrown!
 */
class LocalizationListener extends RequestListener
{
    const PARAMETER_NAME = "locale";

    public function run() {
        $xml = $this->application->getXML()->internationalization;
        if(empty($xml)) throw new ApplicationException("Tag missing/empty in configuration.xml: internationalization");

        // identifies locale
        $localeDetector = new LocaleDetector($xml, $this->request);
        
        // compiles settings
        $detector = new SettingsDetector($this->application, $xml, $localeDetector);
        $settings = $detector->getSettings();
        
        // sets internationalization settings (throws LocaleException)
        new Lucinda\Internationalization\Reader($settings);

        // saves locale in session
        if($localeDetector->getDetectionMethod() == "session") {
            $this->request->getSession()->set(self::PARAMETER_NAME, $settings->getLocale());
        }
    }
}