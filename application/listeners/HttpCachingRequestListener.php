<?php
require_once("vendor/lucinda/http-caching/loader.php");
require_once("src/caching/CachingPolicyFinder.php");
require_once("src/caching/CacheableDriver.php");

/**
 * Reads xml HTTP caching policies, binds HTTP Caching API to Servlets API and sets up CachingPolicy that matches currently requested resource
 * into "caching_policy" Request attribute. After a caching policy is found, it performs validation following IETF specifications. Validation 
 * result is a HTTP status:
 * - 304 Not Modified: resource hasn't changed from local cache version (etag/last modified date match). Header is sent and code exits.
 * - 412 Precondition Failed: conditional headers failed to match resource. Header is sent and code exits.
 * - 200 OK: resource has changed and server is expected a full response
 * 
 * Only basic policies are defined:
 * - no_cache: if set to 1 this means whatever falls under it will never use HTTP caching
 * - expiration: if set (to an unsigned value greater than zero) this means local browser cache object will require revalidation in X seconds
 * - class: name of CacheableDriver class that will map requested resource to an ETAG / Last-Modified-Date for later validation.
 * For more custom settings, feel free to modify this class!
 *
 *  The XML to define HTTP caching policies used by application:
 *  <http_caching (no_cache="1|0" expiration="{SECONDS}" class="{CacheableDriver}")?>
 *  	<route url="{ROUTE}" (no_cache="1|0" expiration="{SECONDS}" class="{CacheableDriver}")?>
 *  	...
 *  </http_caching>
 *  
 *  By default policy used will be the default one (set in properties of http_caching tag). If one desires different policies per route, they must
 *  be setup in a <route> subtag. If requested resource URL matches an "url" property @ http_caching.route, its policies will override global ones.
 */
class HttpCachingRequestListener extends RequestListener {
	public function run() {
		// detects caching_policy
		$policy = $this->getCachingPolicy();
		$this->request->setAttribute("caching_policy", $policy);
		
		// perform validation
		if(!$policy->getCachingDisabled() && $policy->getCacheableDriver()) {
			$cacheRequest = new CacheRequest();
			if($cacheRequest->isValidatable()) {
				$validator = new CacheValidator($cacheRequest);
				$httpStatusCode = $validator->validate($policy->getCacheableDriver());
				if($httpStatusCode==304) {
					header("HTTP/1.1 304 Not Modified");
					exit();
				} else if($httpStatusCode==412) {
					header("HTTP/1.1 412 Precondition Failed");
					exit();
				} else {
					// will need to revalidate
					$this->request->setAttribute("caching_request", $cacheRequest);
				}
			}
		}
	}
	
	/**
	 * Gets caching policy 
	 * 
	 * @throws ApplicationException
	 * @return CachingPolicy
	 */
	private function getCachingPolicy() {
		// get global caching policy
		$caching = $this->application->getXML()->http_caching;
		if(!$caching) throw new ApplicationException("Entry missing in configuration.xml: http_caching");
		$finder = new CachingPolicyFinder($caching, $this->application, $this->request);
		$globalPolicy = $finder->getPolicy();
		
		// get route-specific caching policy (whatever is not specifically
		$pv = new PageValidator($this->request->getURI()->getPage(), $this->application);
		$page = $pv->getPage();
		$tmp = (array) $this->application->getXML()->http_caching;
		if(!empty($tmp["route"])) {
			$elements = is_array($tmp["route"])?$tmp["route"]:array($tmp["route"]);
			foreach($elements as $info) {
				$route = $info["url"];
				if($route === null) throw new ApplicationException("Property missing in http_caching.route tag: url");
				if($route == $page) {
					$finder = new CachingPolicyFinder($info, $this->application, $this->request);
					$routePolicy = $finder->getPolicy();
					
					$finalPolicy = new CachingPolicy();
					$finalPolicy->setCachingDisabled($routePolicy->getCachingDisabled()!==null?$routePolicy->getCachingDisabled():$globalPolicy->getCachingDisabled());
					$finalPolicy->setExpirationPeriod($routePolicy->getExpirationPeriod()!==null?$routePolicy->getExpirationPeriod():$globalPolicy->getExpirationPeriod());
					$finalPolicy->setCacheableDriver($routePolicy->getCacheableDriver()!==null?$routePolicy->getCacheableDriver():$globalPolicy->getCacheableDriver());
					return $finalPolicy;
				}
			}
		}
		
		return $globalPolicy;
	}
}