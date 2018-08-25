<?php
require_once("vendor/lucinda/internationalization/src/Reader.php");
require_once("vendor/lucinda/framework-engine/src/internationalization/LocalizationBinder.php");
require_once("application/models/internationalization/Translate.php");

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
    public function run() {
        new LocalizationBinder($this->application, $this->request);
    }
}