<?php
/**
 * Simple (HTML) view wrapper, used whenever an explicit wrapper for content type was not set.
 */
class HtmlResolver extends Lucinda\MVC\STDOUT\ViewResolver {
    public function getContent() {
        $output = "";
        $view = $this->application->getViewsPath()."/".$this->response->getView();
		if($view) {
			$_VIEW = $this->response->attributes()->toArray();
			$view .= ".php";
			if(!file_exists($view)) throw new Lucinda\MVC\STDOUT\ServletException("View file not found: ".$view);
			
			
			ob_start();
			require_once($view);
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}
}