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
	 * @param Request $request ServletsAPI object that encapsulates relevant information about client request
	 */
	public function __construct(SimpleXMLElement $xml, Application $application, Request $request) {
	    $format = $this->getDisplayFormat($application, $request);
	    $extension = $format->getExtension();
		$environment = $application->getAttribute("environment");
		$renderer = (!empty($xml) && !empty($xml->$environment)?$xml->$environment->renderer:null);
		if(!$renderer || !isset($renderer->$extension)) {
			return; // it is allowed to render nothing
		}
		
		$this->setRenderer($renderer->$extension, $format, ((string) $renderer["display_errors"]?true:false));
	}
	
	/**
	 * Detects display format based on Application and Request objects
	 * 
	 * @param Application $application ServletsAPI object that encapsulates relevant information from configuration.xml
	 * @param Request $request ServletsAPI object that encapsulates relevant information about client request
	 * @return Format Display format information.
	 */
	private function getDisplayFormat(Application $application, Request $request) {
	    $contentType = $request->getValidator()->getContentType();
		$formats = $application->getFormats();
		foreach($formats as $format) {
		    if(strpos($contentType, $format->getContentType())===0) {
		        return $format;
		    }
		}
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
