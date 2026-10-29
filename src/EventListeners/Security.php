<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\OAuth2\ServiceBinder;
use Lucinda\Framework\RequestBinder;
use Lucinda\Framework\XmlDetector;
use Lucinda\MVC\EventListener\MultiFaceted;
use Lucinda\MVC\FacetCollection;
use Lucinda\MVC\Response\Redirect;
use Lucinda\MVC\TerminationException;
use Lucinda\Project\Facets\Security as SecurityFacet;
use Lucinda\Project\Facets\MultiFactor as MultiFactorFacet;
use Lucinda\Project\DAO\OAuth2States;
use Lucinda\STDOUT\Cookies;
use Lucinda\STDOUT\Request;
use Lucinda\OAuth2\Wrapper as OAuth2Wrapper;
use Lucinda\STDERR\Service\ValidatedRequest;
use Lucinda\WebSecurity\Configuration\RolesDetector;
use Lucinda\WebSecurity\OAuth2State;
use Lucinda\WebSecurity\Packets\GuestUser;
use Lucinda\WebSecurity\Request as WebSecurityRequest;
use Lucinda\Project\OAuth2State as OAuth2StateImplementation;
use Lucinda\WebSecurity\Packets\LoggedInUser;
use Lucinda\WebSecurity\Packets\MultiFactor as MultiFactorPacket;
use Lucinda\WebSecurity\Packets\Security as SecurityPacket;
use Lucinda\WebSecurity\Packets\Throttling as ThrottlingPacket;
use Lucinda\WebSecurity\Packets\Packet;
use Lucinda\WebSecurity\Security\MultiFactorAuthentication\ResultStatus as MultiFactorResultStatus;
use Lucinda\WebSecurity\Wrapper as SecurityWrapper;

/**
 * Executes web security and publishes authentication and MFA request facets
 *
 * Browser applications follow packet callbacks through redirects. REST
 * applications expose callbacks through their security facet. OAuth2 state
 * uses the same SQL and browser-cookie implementation in both cases.
 */
final class Security implements MultiFaceted
{
    const XML_PATH = "xml/stdout/security";

    /**
     * Current HTTP request supplying security input and application context
     */
    private Request $request;

    /**
     * Resolved route used for authentication and authorization matching
     */
    private ValidatedRequest $validatedRequest;

    /**
     * Incoming browser cookies used to validate OAuth2 login attempts
     */
    private Cookies $cookies;

    /**
     * Captures the request, resolved route, and browser cookie access
     *
     * @param Request $request Current HTTP request
     * @param ValidatedRequest $validatedRequest Route selected by request validation
     * @param Cookies $cookies Incoming cookies used by OAuth2 state verification
     */
    public function __construct(Request $request, ValidatedRequest $validatedRequest, Cookies $cookies)
    {
        $this->request = $request;
        $this->validatedRequest = $validatedRequest;
        $this->cookies = $cookies;
    }

    /**
     * Executes security and publishes general security and MFA context
     *
     * @return FacetCollection Facets registered under their concrete class names
     * @throws TerminationException If the outcome requires a browser redirect
     * @throws \Throwable If configuration, persistence, or security processing fails
     */
    public function run(): FacetCollection
    {
        // initialize response
        $collection = new FacetCollection();
        $securityFacet = new SecurityFacet();
        $collection->add(SecurityFacet::class, $securityFacet);
        $multiFactorFacet = new MultiFactorFacet();
        $collection->add(MultiFactorFacet::class, $multiFactorFacet);

        // execute security logic
        $security = new SecurityWrapper(
            $this->getXML(),
            $this->getRequest(),
            $this->getOAuth2Drivers(),
            $this->getOAuth2State(),
            $this->getRolesDetector()
        );
        $packet = $security->getOutcome();

        // act on the packet result
        if ($packet === null) {
            return $collection;
        }
        if ($packet instanceof LoggedInUser) {
            $securityFacet->setUserID($packet->getUserID());
        }
        $securityFacet->setAccessToken($packet->getAccessToken());
        if ($packet instanceof GuestUser || $packet instanceof LoggedInUser) {
            $securityFacet->setCsrfToken($packet->getCsrfToken());
        }
        if ($multiFactorPacket = $this->checkMultiFactorAuthentication($packet)) {
            $multiFactorFacet->setPacket($multiFactorPacket);
        } elseif ($callback = $packet->getCallback()) {
            if ($this->isRestful()) {
                $securityFacet->setCallback($callback);
            } else {
                $this->handleRedirect($packet);
            }
        }
        return $collection;
    }

    /**
     * Gets the application's security configuration
     *
     * @return \SimpleXMLElement Complete XML document containing security settings
     * @throws \Throwable If the configuration cannot be loaded
     */
    private function getXML(): \SimpleXMLElement
    {
        $detector = new XmlDetector(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.self::XML_PATH);
        return $detector->getXML();
    }

    /**
     * Gets the HTTP request adapted for web-security processing
     *
     * @return WebSecurityRequest Request with the validated route and bearer token
     */
    private function getRequest(): WebSecurityRequest
    {
        $binder = new RequestBinder($this->request, $this->validatedRequest->getRoute());
        return $binder->getResult();
    }

    /**
     * Gets configured provider clients adapted to the security API
     *
     * @return array<string,\Lucinda\WebSecurity\OAuth2Service> Services keyed by provider name
     * @throws \Throwable If provider configuration cannot be loaded or adapted
     */
    private function getOAuth2Drivers(): array
    {
        $filePath = $this->getOAuth2XMLPath();
        if ($filePath === null) {
            return []; // project does not use oauth2 or local environment doesn't support it
        }

        // get raw oauth2 drivers
        $xmlDetector = new XmlDetector(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.$filePath);
        $wrapper = new OAuth2Wrapper($xmlDetector->getXML());
        $oauth2Drivers = $wrapper->getDriver();

        // convert them to security api usage
        $serviceBinder = new ServiceBinder($oauth2Drivers);
        return $serviceBinder->getServices();
    }

    /**
     * Gets browser-bound SQL state storage when OAuth2 is enabled
     *
     * The SQL data-source listener must run before state persistence is used.
     * This implementation does not depend on the session listener.
     *
     * @return OAuth2State|null Shared implementation for browser and REST apps, or null when disabled
     */
    private function getOAuth2State(): ?OAuth2State
    {
        if ($this->getOAuth2XMLPath() === null) {
            return null; // project does not use oauth2 or local environment doesn't support it
        }
        return new OAuth2StateImplementation(new OAuth2States(), $this->cookies);
    }

    /**
     * Gets the optional provider configuration path relative to the project
     *
     * @return string|null XML path without its extension, or null to disable OAuth2
     */
    private function getOAuth2XMLPath(): ?string
    {
        // return "xml/stdout/oauth2";
        return null;
    }

    /**
     * Gets indexed route-role policies when XML authorization is configured
     *
     * @return RolesDetector|null Route-role detector, or null when not configured
     * @throws \Throwable If route-role configuration cannot be loaded
     */
    private function getRolesDetector(): ?RolesDetector
    {
        $filePath = $this->getRoutesXMLPath();
        if ($filePath === null) {
            return null;
        }

        $xmlDetector = new XmlDetector(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.$filePath);
        return new RolesDetector($xmlDetector->getXML(), "routes", "route", "id");
    }

    /**
     * Gets the optional route-role configuration path relative to the project
     *
     * @return string|null XML path without its extension, or null to disable XML roles
     */
    private function getRoutesXMLPath(): ?string
    {
        // return "xml/stdout/routes";
        return null;
    }

    /**
     * Checks whether callbacks should be exposed instead of followed
     *
     * @return bool True for an application configured to return REST responses
     */
    private function isRestful(): bool
    {
        return false;
    }

    /**
     * Terminates the request with the packet's callback redirect
     *
     * @param Packet $packet Outcome containing a nonempty callback destination
     * @throws TerminationException Always, carrying the redirect response
     */
    private function handleRedirect(Packet $packet): void
    {
        $uri = $packet->getCallback();
        if (
            $packet instanceof MultiFactorPacket || 
            $packet instanceof SecurityPacket || 
            $packet instanceof ThrottlingPacket
        ) {
           $uri .= (str_contains($uri, "?")?"&":"?")."status=".$packet->getStatus()->name;
        }
        $redirect = new Redirect($uri);
        $redirect->setPreventCaching(true);
        throw new TerminationException($redirect);
    }

    /**
     * Selects an MFA form outcome already on its designated rendering route
     *
     * @param Packet $packet Security outcome whose callback would otherwise be followed
     * @return MultiFactorPacket|null Setup or challenge data for this route, or null for navigation
     */
    private function checkMultiFactorAuthentication(Packet $packet): ?MultiFactorPacket
    {
        $fullValidatedRoute = $this->request->getURI()->getContextPath()."/".$this->validatedRequest->getRoute();
        if (
            $packet instanceof MultiFactorPacket && 
            in_array($packet->getStatus(), [
            MultiFactorResultStatus::REQUIRED,
            MultiFactorResultStatus::SETUP_REQUIRED
            ]) &&
            ($fullValidatedRoute === $packet->getCallback())) {
            return $packet;
        }
        return null;
    }
}
