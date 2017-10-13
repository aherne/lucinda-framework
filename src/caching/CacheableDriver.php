<?php
/**
 * Driver binding Servlets API and HTTP Caching API, fed with application and request information. Children have the responsibility
 * of implementing setters for etag and last modified time according to their specific business needs.
 */
abstract class CacheableDriver implements Cacheable {
	/**
	 * @var Application
	 */
	protected $application;
	/**
	 * @var Request
	 */
	protected $request;
	
	/**
	 * @var string
	 */
	protected $etag;
	
	/**
	 * @var integer
	 */
	protected $last_modified_time;
	
	public function __construct(Application $application, Request $request) {
		$this->application = $application;
		$this->request = $request;
		$this->setTime();
		$this->setEtag();
	}
	
	/**
	 * Sets value of last modified time of requested resource
	 */
	abstract protected function setTime();
	
	/**
	 * {@inheritDoc}
	 * @see Cacheable::getTime()
	 */
	public function getTime() {
		return $this->last_modified_time;
	}
	
	/**
	 * Sets value of etag matching requested resource
	 */
	abstract protected function setEtag();
	
	/**
	 * {@inheritDoc}
	 * @see Cacheable::getEtag()
	 */
	public function getEtag() {
		return $this->etag;
	}
}