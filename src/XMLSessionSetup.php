<?php
/**
 * Sets up session options based on XML tag:
 * <session expiration="10" is_http_only="1" is_https_only="1" handler="{value}" .../>
 *
 * Where:
 * - expiration: (optional) seconds until session expires. If not set, session will expire as server-default.
 * - is_http_only: (optional) whether or not to set session cookie as HttpOnly (can be 0 or 1; 0 is default).
 * - is_https_only: (optional) whether or not to set session cookie as HTTPS only (can be 0 or 1; 0 is default).
 * - handler: (optional) name of class implementing SessionHandlerInterface to which session handling will be delegated
 * to. Its file must be located in folder application/models.
 */
class XMLSessionSetup {
    const HANDLER_FOLDER = "application/models";

    private $options;
    private $handler;

    public function __construct(SimpleXMLElement $xml) {
        $this->setSecurityOptions($xml);
        $this->setHandler($xml);
    }

    /**
     * Sets up session security info based on XML
     *
     * @param SimpleXMLElement $xml
     * @return SessionSecurityOptions
     */
    private function setSecurityOptions(SimpleXMLElement $xml) {
        $sso = new SessionSecurityOptions();
        $expirationTime = (integer) $xml["expiration"];
        if($expirationTime) $sso->setExpiredTime($expirationTime);
        $isHttpOnly = (integer) $xml["is_http_only"];
        if($isHttpOnly) $sso->setSecuredByHTTPheaders(true);
        $isHttpsOnly = (integer) $xml["is_https_only"];
        if($isHttpsOnly) $sso->setSecuredByHTTPS(true);
        $this->options = $sso;
    }

    /**
     * Gets session security info.
     *
     * @return SessionSecurityOptions
     */
    public function getSecurityOptions() {
        return $this->options;
    }

    /**
     * Sets instance of handler based on XML
     *
     * @param SimpleXMLElement $xml
     * @throws ServletException If handler file/class not found or latter is not instanceof SessionHandlerInterface
     * @return SessionHandlerInterface
     */
    private function setHandler(SimpleXMLElement $xml) {
        $handlerName = (string) $xml["handler"];
        if(!$handlerName) return null;
        $file = self::HANDLER_FOLDER."/".$handlerName.".php";
        if(!file_exists($file)) throw new ServletException("Handler file not found: ".$file);
        require_once($file);
        if(!class_exists($handlerName)) throw new ServletException("Handler class not found: ".$handlerName);
        $object = new $handlerName();
        if(!($object instanceof SessionHandlerInterface))  throw new ServletException("Handler must be instance of SessionHandlerInterface!");
        $this->handler = $object;
    }

    /**
     * Gets instance of class to which session handling will be delegated to.
     *
     * @return SessionHandlerInterface
     */
    public function getHandler() {
        return $this->handler;
    }
}