<?php
require_once("CachingPolicyFinder.php");

/**
 * Locates CachingPolicy in XML based on contents of http_caching tag. Binds route-based settings (if any) with
 * global caching settings into a CachingPolicy object.
 */
class CachingPolicyBinder {
    private $policy;

    /**
     * CachingPolicyBinder constructor.
     *
     * @param SimpleXMLElement $xml XML document containing http caching API bindings
     * @param string $route Relative page path requested by client
     */
    public function __construct(Application $application, Request $request) {
        $this->setPolicy($application, $request);
    }

    /**
     * Detects caching policy based on contents of http_caching tag and sets a CachingPolicy object in result
     *
     * @param Application $application Encapsulates application settings @ ServletsAPI.
     * @param Request $request Encapsulates request information.
     * @throws ApplicationException If XML is incorrect formatted.
     */
    private function setPolicy(Application $application, Request $request) {
        $this->policy = $this->getGlobalPolicy($application, $request);
        $specificPolicy = $this->getSpecificPolicy($application, $request);
        if($specificPolicy) {
            if($specificPolicy->getCachingDisabled()!==null) {
                $this->policy->setCachingDisabled($specificPolicy->getCachingDisabled());
            }
            if($specificPolicy->getExpirationPeriod()!==null) {
                $this->policy->setExpirationPeriod($specificPolicy->getExpirationPeriod());
            }
            if($specificPolicy->getCacheableDriver()!==null) {
                $this->policy->setCacheableDriver($specificPolicy->getCacheableDriver());
            }
        }
    }

    /**
     * Detects generic CachingPolicy (applying by default to all routes)
     *
     * @param Application $application Encapsulates application settings @ ServletsAPI.
     * @param Request $request Encapsulates request information.
     * @throws ApplicationException If XML is incorrect formatted.
     */
    private function getGlobalPolicy(Application $application, Request $request) {
        $caching = $application->getXML()->http_caching;
        if(!$caching) throw new ApplicationException("Entry missing in configuration.xml: http_caching");
        $finder = new CachingPolicyFinder($caching, $application, $request);
        return $finder->getPolicy();
    }

    /**
     * Detects route-specific CachingPolicy (if any)
     *
     * @param Application $application Encapsulates application settings @ ServletsAPI.
     * @param Request $request Encapsulates request information.
     * @throws ApplicationException If XML is incorrect formatted.
     */
    private function getSpecificPolicy(Application $application, Request $request) {
        $page = $request->getValidator()->getPage();
        $tmp = (array) $application->getXML()->http_caching;
        if(!empty($tmp["route"])) {
            $elements = is_array($tmp["route"])?$tmp["route"]:array($tmp["route"]);
            foreach($elements as $info) {
                $route = $info["url"];
                if($route === null) throw new ApplicationException("Property missing in http_caching.route tag: url");
                if($route == $page) {
                    $finder = new CachingPolicyFinder($info, $application, $request);
                    return $finder->getPolicy();
                }
            }
        }
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