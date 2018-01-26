<?php
/**
 * Locates and instances error renderer based on XML content and information already encapsulated by Application object.
 */
class ErrorRendererFinder {
	const RENDERERS_FOLDER = "application/models/error_renderers";
	
	protected $renderer;
	
	/**
	 * Reads XML tag errors.{environment}.renderer, then finds and saves renderer found.
	 *
	 * @param SimpleXMLElement $xml XML tag reference object.
	 * @param Application $application ServletsAPI object that encapsulates relevant information from configuration.xml
	 */
	public function __construct(SimpleXMLElement $xml, Application $application) {
		$extension = $this->detectExtension($application);
		$environment = $application->getAttribute("environment");
		$renderer = (!empty($xml) && !empty($xml->$environment)?$xml->$environment->renderer:null);
		if(!$renderer || !isset($renderer->$extension)) {
			return; // it is allowed to render nothing
		}
		
		$this->setRenderer($renderer->$extension, $application->getFormatInfo($extension), ((string) $renderer["display_errors"]?true:false));
	}
	
	/**
	 * Detects display format (extension) based on requested page semantics and configuration.xml info encapsulated by Application object
	 * 
	 * @param Application $application ServletsAPI object that encapsulates relevant information from configuration.xml
	 * @return string Value of extension found (eg: html).
	 */
	private function detectExtension(Application $application) {
		$extension = $application->getDefaultExtension();
		$pathRequested = substr(str_replace("?".$_SERVER["QUERY_STRING"],"",$_SERVER["REQUEST_URI"]),1);
		if(!$pathRequested) $pathRequested = $application->getDefaultPage();
		if(!$application->getAutoRouting()) {
		    if(!$application->hasRoute($pathRequested)) {
		        $tblRoutes = $application->getRoutes();
		        foreach($tblRoutes as $objRoute) {
		            if(strpos($objRoute->getPath(), "(")!==false) {
		                preg_match_all("/(\(([^)]+)\))/", $objRoute->getPath(), $matches);
		                $pattern = "/^".str_replace($matches[1],"([^\/]+)",str_replace("/","\/",$objRoute->getPath()))."$/";
		                if(preg_match_all($pattern,$pathRequested,$results)==1) {
		                    if($objRoute->getFormat()) {
		                        $extension = $objRoute->getFormat();
		                    }
		                    break;
		                }
		            }
		        }
		    } else {
		        $objRoute = $application->getRouteInfo($pathRequested);
		        if($objRoute->getFormat()) {
		            $extension = $objRoute->getFormat();
		        }
		    }
		}

		return $extension;
	}
	
	/**
	 * Finds renderer in container XML tag based on display format(extension), instances then saves it for latter reference.
	 * 
	 * @param SimpleXMLElement $xml Contents of errors.{environment}.renderer tag.
	 * @param Format $format Encapsulates information about display format (extension & content type)
	 * @param boolean $displayErrors Whether or not error details should be shown on screen.
	 * @param string $characterEncoding Character encoding to be used in display (relevant for formats such as html, xml or json. 
	 */
	protected function setRenderer(SimpleXMLElement $xml, Format $format, $displayErrors) {
		$rendererClassName = ucwords($format->getExtension())."Renderer";
		$rendererFile = self::RENDERERS_FOLDER."/".$rendererClassName.".php";
		if(file_exists($rendererFile)) {
			require_once($rendererFile);
			$this->renderer = new $rendererClassName($displayErrors, $format->getCharacterEncoding());
		}
	}
	
	/**
	 * Gets error renderer found.
	 * 
	 * @return ErrorRenderer
	 */
	public function getRenderer() {
		return $this->renderer;
	}
}
