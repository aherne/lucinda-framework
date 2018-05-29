<?php
class SettingsDetector {
    private $settings;
    
    public function __construct(Application $application, SimpleXMLElement $xml, LocaleDetector $locale) {
        $this->setSettings($application, $xml, $locale);
    }
    
    private function setSettings(Application $application, SimpleXMLElement $xml, LocaleDetector $locale) {
        // compiles settings
        $this->settings = new Lucinda\Internationalization\Settings($locale->getDetectedLocale());
        $charset = $application->getFormatInfo($application->getDefaultExtension())->getCharacterEncoding();
        if($charset) $this->settings->setCharset($charset);
        $domain = (string) $xml["domain"];
        if($domain) $this->settings->setDomain($domain);
        $folder = (string) $xml["folder"];
        if($folder) $this->settings->setFolder($folder);
        
        // if locale has no translations on disk, override it with default
        $file = $this->settings->getFolder().DIRECTORY_SEPARATOR.$this->settings->getLocale().DIRECTORY_SEPARATOR."LC_MESSAGES".DIRECTORY_SEPARATOR.$settings->getDomain().".mo";
        if(!file_exists($file)) {
            $settings->setLocale($locale->getDefaultLocale());
        }
    }
    
    public function getSettings() {
        return $this->settings;
    }
}