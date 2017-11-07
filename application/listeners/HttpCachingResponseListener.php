<?php
/**
 * Binds CachingPolicy object (detected based on XML) to a CacheResponse object, which encapsulates HTTP caching headers to include in response.
 * Code execution continue and when response is rendered caching response headers will be added, too.
 * REQUIRES HttpCachingRequestListener TO ALLOW BIDIRECTIONAL CACHE COMMUNICATION BETWEEN CLIENT AND SERVER! 
 */
class HttpCachingResponseListener extends ResponseListener {
	public function run() {
		$policy = $this->request->getAttribute("caching_policy");
		if(!$policy) throw new ApplicationException("No caching policy defined!");
		if(!$policy->getCachingDisabled() && $policy->getCacheableDriver()) {
			$cacheable = $policy->getCacheableDriver();
			
			$cacheResponse = new CacheResponse();
			if($cacheable->getEtag()) $cacheResponse->setEtag($cacheable->getEtag());
			if($cacheable->getTime()) $cacheResponse->setLastModified($cacheable->getTime());
			if($policy->getExpirationPeriod()) $cacheResponse->setMaxAge($policy->getExpirationPeriod());
			$headers = $cacheResponse->getHeaders();
			foreach($headers as $name=>$value) {
				$this->response->headers()->set($name, $value);
			}
		}
	}
}