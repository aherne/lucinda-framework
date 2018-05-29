<?php
require_once("CachingPolicyFinder.php");

class CachingPolicyBinder {
    private $policy;
    
    public function __construct(Application $application, Request $request) {
        $this->policy = $this->getSpecificPolicy($application, $request);
        if($this->policy==null) {
            $this->policy = $this->getGlobalPolicy($application, $request);        
        }
    }
    
    private function getGlobalPolicy(Application $application, Request $request) {
        $caching = $application->getXML()->http_caching;
        if(!$caching) throw new ApplicationException("Entry missing in configuration.xml: http_caching");
        $finder = new CachingPolicyFinder($caching, $application, $request);
        return $finder->getPolicy();
    }
    
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
                    $routePolicy = $finder->getPolicy();
                    
                    $finalPolicy = new CachingPolicy();
                    $finalPolicy->setCachingDisabled($routePolicy->getCachingDisabled()!==null?$routePolicy->getCachingDisabled():$globalPolicy->getCachingDisabled());
                    $finalPolicy->setExpirationPeriod($routePolicy->getExpirationPeriod()!==null?$routePolicy->getExpirationPeriod():$globalPolicy->getExpirationPeriod());
                    $finalPolicy->setCacheableDriver($routePolicy->getCacheableDriver()!==null?$routePolicy->getCacheableDriver():$globalPolicy->getCacheableDriver());
                    return $finalPolicy;
                }
            }
        }
    }
    
    public function getPolicy() {
        return $this->policy;
    }
}