<?php
/**
 * Binds CachingPolicy object (detected based on XML) and CacheRequest object (detected based on caching headers) set up by HttpCachingRequestListener
 * to a CacheResponse object, which encapsulates HTTP caching headers to include in response.
 * If revalidation was requested, conditionals exist and equal ETAG / last modified time that match requested resource, a 304 Not Modified header is sent
 * including caching response headers and code exits. Otherwise, code execution continue and when response is rendered caching response headers will be
 * added, too.
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
			$cacheRequest = $this->request->getAttribute("caching_request");
			$headers = $cacheResponse->getHeaders();
			if($cacheRequest && $this->isNotModified($cacheRequest, $cacheable)) {
				header("HTTP/1.1 304 Not Modified");
				foreach($headers as $name=>$value) {
					header($name.": ".$value);
				}
				exit();
			} else {
				foreach($headers as $name=>$value) {
					$this->response->headers()->set($name, $value);
				}
			}
		}
	}
	
	/**
	 * Checks if requested resource hasn't changed from version known by local (browser) cache.
	 * 
	 * @param CacheRequest $cacheRequest Encapsulates caching headers sent by client in request.
	 * @param Cacheable $cacheable
	 * @return boolean
	 */
	private function isNotModified(CacheRequest $cacheRequest, Cacheable $cacheable) {
		$i = 0;
		
		if($cacheRequest->getNotMatchingEtag()) {
			if($cacheRequest->getNotMatchingEtag()!=$cacheable->getEtag()) {
				return false;
			} else {
				$i++;
			}
		}
		
		if($cacheRequest->getMatchingEtag()) {
			if($cacheRequest->getMatchingEtag()!=$cacheable->getEtag()) {
				return false;
			} else {
				$i++;
			}
		}
		
		if($cacheRequest->getModifiedSince()) {
			if($cacheRequest->getModifiedSince()!=$cacheable->getTime()) {
				return false;
			} else {
				$i++;
			}
		}
		
		if($cacheRequest->getNotModifiedSince()) {
			if($cacheRequest->getNotModifiedSince()!=$cacheable->getTime()) {
				return false;
			} else {
				$i++;
			}
		}
		
		if($i>0) return true;
		else return false;
	}
}