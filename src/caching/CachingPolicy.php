<?php
/**
 * Encapsulates basic HTTP caching policies rules.
 */
class CachingPolicy {	
	private $no_cache;
	private $cacheableDriver;
	private $expires;
	
	/**
	 * Sets caching as disabled
	 * 
	 * @param null|boolean $value Possible values: TRUE, FALSE or NULL (which means UNKNOWN)
	 */
	public function setCachingDisabled($value) {
		$this->no_cache = $value;
	}
	
	/**
	 * Checks whether or not caching is disabled.
	 * 
	 * @return null|boolean Possible values: TRUE, FALSE or NULL (which means UNKNOWN)
	 */
	public function getCachingDisabled() {
		return $this->no_cache;
	}
	
	/**
	 * Sets period from original server response by which entry expires in local (browser) cache.
	 * 
	 * @param null|integer $expires Possible values: an unsigned integer (seconds) or NULL (which means UNKNOWN)
	 */
	public function setExpirationPeriod($expires) {
		$this->expires = $expires;
	}
	
	/**
	 * Gets period from original server response by which entry expires in local (browser) cache. 
	 * 
	 * @return null[|integer Possible values: an unsigned integer (seconds) or NULL (which means UNKNOWN)
	 */
	public function getExpirationPeriod() {
		return $this->expires;
	}
	
	/**
	 * Sets driver that must be able to map requested resource to an ETAG/LAST-MODIFIED-DATE
	 * 
	 * @param CacheableDriver $driver A Cacheable implementation to be used by Lucinda Framework applications.
	 */
	public function setCacheableDriver(CacheableDriver $driver) {
		$this->cacheableDriver = $driver;
	}
	
	/**
	 * Gets driver that must be able to map requested resource to an ETAG/LAST-MODIFIED-DATE
	 * 
	 * @return CacheableDriver A Cacheable implementation to be used by Lucinda Framework applications.
	 */
	public function getCacheableDriver() {
		return $this->cacheableDriver;
	}
}