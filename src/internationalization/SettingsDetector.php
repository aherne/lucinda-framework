<?php
/**
 * Binds Internationalization API Settings to <internationalization> tag content.
 */
class SettingsDetector {
    private $settings;
    
    /**
     * @param string $charset Application default character set detected from XML.
     * @param SimpleXMLElement $xml Content of <internationalization> tag.
     * @param LocaleDetector $locale Locale detected previously by matching <internationalization> tag content with user request.
     */
    public function __construct($charset, SimpleXMLElement $xml, LocaleDetector $locale) {
        $this->setSettings($charset, $xml, $locale);
    }
    /**
     * Compiles and saves an Internationalization API Settings object based on arguments.
     * 
     * @param string $charset Application default character set detected from XML.
     * @param SimpleXMLElement $xml Content of <internationalization> tag.
     * @param LocaleDetector $locale Locale detected previously by matching <internationalization> tag content with user request.
     */
    
    private function setSettings($charset, SimpleXMLElement $xml, LocaleDetector $locale) {
        // compiles settings
        $this->settings = new Lucinda\Internationalization\Settings($locale->getDetectedLocale());
        if($charset) $this->settings->setCharset($charset);
        $domain = (string) $xml["domain"];
        if($domain) $this->settings->setDomain($domain);
        $folder = (string) $xml["folder"];
        if($folder) $this->settings->setFolder($folder);
        
        // if locale has no translations on disk, override it with default
        $file = $this->settings->getFolder().DIRECTORY_SEPARATOR.$this->settings->getLocale().DIRECTORY_SEPARATOR."LC_MESSAGES".DIRECTORY_SEPARATOR.$this->settings->getDomain().".mo";
        if(!file_exists($file)) {
            $this->settings->setLocale($locale->getDefaultLocale());
        }
    }
    
    /**
     * Gets compiled Internationalization API Settings object
     * 
     * @return \Lucinda\Internationalization\Settings
     */
    public function getSettings() {
        return $this->settings;
    }
}