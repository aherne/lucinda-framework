<?php
class ViewLanguageWrapper {
    private $compilationFile;
    
    public function __construct(Application $application, Response $response) {
        $this->setCompilationFile($application, $response);
    }
    
    private function setCompilationFile(Application $application, Response $response) {
        // get compilations folder
        $environment = $application->getAttribute("environment");
        $compilationsFolder = (string) $application->getXML()->application->paths->compilations->$environment;
        if(!$compilationsFolder) throw new ApplicationException("Compilations folder not defined!");
        $tagsFolder = (string) $application->getXML()->application->paths->tags;
        $extension = (string) $application->getXML()->application->templates_extension;
        
        // gets view file
        $viewFile = $response->getView();
        if(strpos($viewFile, $application->getViewsPath())===0) {
            $viewFile = substr($viewFile, strlen($application->getViewsPath())+1);
        }
        
        // compiles templates recursively into a single compilation file
        $vlp = new ViewLanguageParser($application->getViewsPath(), $extension, $compilationsFolder, $tagsFolder);
        $this->compilationFile = $vlp->compile($viewFile);
    }
    
    public function getCompilationFile() {
        return $this->compilationFile;
    }
}