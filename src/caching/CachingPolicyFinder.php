<?php
require_once("CachingPolicy.php");

/**
 * Encapsulates detection of caching policy from a relevant XML line.
 */
class CachingPolicyFinder {
	private $policy;
	
	/**
	 * @param SimpleXMLElement $xml Tag that's holding policies.
	 * @param Application $application 
	 * @param Request $request
	 */
	public function __construct(SimpleXMLElement $xml, Application $application, Request $request) {
		$this->setPolicy($xml, $application, $request);
	}
	
	/**
	 * Generates and saves a CachingPolicy object
	 * 
	 * @param SimpleXMLElement $xml Tag that's holding policies.
	 * @param Application $application
	 * @param Request $request
	 */
	private function setPolicy(SimpleXMLElement $xml, Application $application, Request $request) {
		$this->policy = new CachingPolicy();
		$this->policy->setCachingDisabled($this->getNoCache($xml));
		$this->policy->setExpirationPeriod($this->getExpirationPeriod($xml));
		$this->policy->setCacheableDriver($this->getCacheableDriver($xml, $application, $request));
	}
	
	/**
	 * Gets "no_cache" property value.
	 * 
	 * @param SimpleXMLElement $xml
	 * @return NULL|boolean
	 */
	private function getNoCache(SimpleXMLElement $xml) {
		if($xml["no_cache"]===null) {
			return null;
		} else {
			return ((string) $xml["no_cache"]?true:false);
		}
	}
	
	/**
	 * Gets "expiration" property value.
	 * 
	 * @param SimpleXMLElement $xml
	 * @return number|NULL
	 */
	private function getExpirationPeriod(SimpleXMLElement $xml) {
		if($xml["expiration"]!==null) {
			return (integer) $xml["expiration"];
		}
		return null;
	}
	
	
	/**
	 * Gets CacheableDriver instance that matches "class" property value.
	 *
	 * @param SimpleXMLElement $xml
	 * @return CacheableDriver|NULL
	 */
	private function getCacheableDriver(SimpleXMLElement $xml, Application $application, Request $request) {
		$driverClass = (string) $xml["class"];
		if($driverClass) {
			// get cacheables folder
			$cacheablesFolder = (string) $application->getXML()->application->paths->cacheables;
			if(!$cacheablesFolder) throw new ApplicationException("Entry missing in configuration.xml: application.paths.cacheables");
			
			// loads and validates class
			$path = $cacheablesFolder."/".$driverClass.".php";
			if(!file_exists($path)) throw new ApplicationException("File not found: ".$path);
			require_once($path);
			if(!class_exists($driverClass)) throw new ApplicationException("Class not found: ".$driverClass);
			
			// sets driver
			return new $driverClass($application, $request);
		}		
		return null;
	}
	
	/**
	 * Gets detected caching policy
	 * 
	 * @return CachingPolicy
	 */
	public function getPolicy() {
		return $this->policy;
	}
}