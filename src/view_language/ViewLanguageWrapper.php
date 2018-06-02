<?php
/**
 * Binds contents of <application> XML tag with detected environment then uses ViewLanguageAPI to produce a PHP file where templating logic in view
 * is compiled into PHP language.
 */
class ViewLanguageWrapper {
    private $compilationFile;
    
    /**
     * @param SimpleXMLElement $xml XML file holding compiler settings.
     * @param string $viewFile View file location (without extension, optionally including views folder path)
     * @param string $environment Detected runtime environment (eg: local, dev, live).
     */
    public function __construct(SimpleXMLElement $xml, $viewFile, $environment) {
        $this->setCompilationFile($xml, $viewFile, $environment);
    }
    
    /**
     * Reads XML then delegates to ViewLanguageAPI to compile a templated view recursively into a PHP file.
     * 
     * @param SimpleXMLElement $xml XML file holding compiler settings.
     * @param string $viewFile View file location (without extension, optionally including views folder path)
     * @param string $environment Detected runtime environment (eg: local, dev, live).
     * @throws ApplicationException
     */
    private function setCompilationFile(SimpleXMLElement $xml, $viewFile, $environment) {
        // get settings necessary in compilation
        $compilationsFolder = (string) $xml->application->paths->compilations->$environment;
        if(!$compilationsFolder) throw new ApplicationException("Compilations folder not defined!");
        $tagsFolder = (string) $xml->application->paths->tags;
        $viewsFolder = (string) $xml->application->paths->views;
        $extension = (string) $xml->application->templates_extension;
        
        // gets view file
        if(strpos($viewFile, $viewsFolder)===0) {
            $viewFile = substr($viewFile, strlen($viewsFolder)+1);
        }
        
        // compiles templates recursively into a single compilation file
        $vlp = new ViewLanguageParser($viewsFolder, $extension, $compilationsFolder, $tagsFolder);
        $this->compilationFile = $vlp->compile($viewFile);
    }
    
    /**
     * Gets compilation file path, where all ViewLanguage templating has been recursively compiled into PHP
     * 
     * @return string
     */
    public function getCompilationFile() {
        return $this->compilationFile;
    }
}