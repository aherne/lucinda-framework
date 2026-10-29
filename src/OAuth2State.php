<?php

namespace Lucinda\Project;

use Lucinda\Project\DAO\OAuth2States;
use Lucinda\STDOUT\Cookies;
use Lucinda\WebSecurity\OAuth2State as OAuth2StateBlueprint;

/**
 * Binds SQL-backed OAuth2 state to the browser initiating authorization
 *
 * Each login attempt receives an independent random proof in a temporary,
 * host-only Secure/HttpOnly cookie. SQL stores only its SHA-256 hash. Separate
 * cookie names allow multiple tabs and providers to authorize concurrently.
 *
 * Requires HTTPS and provider callbacks delivered as top-level GET navigation
 * for SameSite=Lax cookies. No PHP session is used. Native cookie writes allow
 * explicit SameSite settings and failure checks absent from the Cookies facade.
 *
 * @see OAuth2States
 * @see \Lucinda\WebSecurity\OAuth2State
 */
final class OAuth2State implements OAuth2StateBlueprint
{
    /**
     * SQL persistence for authorization attempts and atomic consumption
     */
    private OAuth2States $states;

    /**
     * Cookies received from the browser on the current request
     */
    private Cookies $cookies;

    /**
     * Captures SQL persistence and the current browser's cookies
     *
     * @param OAuth2States $states DAO using the application's default SQL connection
     * @param Cookies $cookies Cookie facade for reading the current request
     */
    public function __construct(OAuth2States $states, Cookies $cookies)
    {
        $this->states = $states;
        $this->cookies = $cookies;
    }

    /**
     * Saves an authorization attempt and sends its independent browser proof
     *
     * Persists before sending the cookie so a database error cannot expose a
     * usable attempt. A cookie-write failure throws; its orphaned database row
     * remains unusable without the proof and is removed by expiration cleanup.
     *
     * @param string $state Full cryptographically random authorization state
     * @param string $vendorName Configured provider bound to the attempt
     * @param int $validUntil Absolute expiration timestamp in Unix seconds
     * @throws \InvalidArgumentException If the attempt is already expired
     * @throws \RuntimeException If response headers were sent or the cookie cannot be written
     * @throws \Throwable If randomness or SQL persistence fails
     */
    public function save(string $state, string $vendorName, int $validUntil): void
    {
        if ($validUntil <= time()) {
            throw new \InvalidArgumentException("OAuth2 state expiration must be in the future");
        }
        if (headers_sent()) {
            throw new \RuntimeException("Cannot save OAuth2 state after response headers were sent");
        }

        $browserProof = bin2hex(random_bytes(32));
        $this->states->save($state, $vendorName, $validUntil, hash("sha256", $browserProof));
        $this->setBrowserProofCookie($this->getCookieName($state), $browserProof, $validUntil);
    }

    /**
     * Validates the browser proof and atomically consumes provider-bound state
     *
     * Missing or malformed cookies are rejected before database access. Only
     * successful consumption expires the cookie; a live provider mismatch can
     * therefore be retried at the correct provider endpoint. Cookie deletion
     * failures throw after consumption, but cannot make the state replayable.
     *
     * @param string $state State received from the OAuth2 callback
     * @param string $vendorName Configured provider handling the callback
     * @return bool True exactly once for a live attempt belonging to this browser and provider
     * @throws \Throwable If SQL consumption or cookie deletion fails
     */
    public function consume(string $state, string $vendorName): bool
    {
        $cookieName = $this->getCookieName($state);
        if (!$this->cookies->contains($cookieName)) {
            return false;
        }
        $browserProof = $this->cookies->get($cookieName);
        if (!is_string($browserProof) || preg_match('/\A[0-9a-f]{64}\z/', $browserProof) !== 1) {
            return false;
        }
        if (!$this->states->consume($state, $vendorName, hash("sha256", $browserProof))) {
            return false;
        }

        $this->setBrowserProofCookie($cookieName, "", time() - 3600);
        return true;
    }

    /**
     * Gets a fixed-length cookie name specific to one authorization attempt
     *
     * The __Host- prefix prevents compliant browsers from accepting a cookie
     * set by a sibling subdomain or restricted to a competing path.
     *
     * @param string $state Full authorization state used to distinguish attempts
     * @return string Host-prefixed cookie name containing the hexadecimal state hash
     */
    private function getCookieName(string $state): string
    {
        return "__Host-lucinda_oauth2_".hash("sha256", $state);
    }

    /**
     * Sends or expires a host-only cookie using identical security attributes
     *
     * Outgoing cookies do not modify the incoming request cookie collection.
     * The browser must return its proof on a subsequent callback request.
     *
     * @param string $cookieName Name identifying the authorization attempt
     * @param string $browserProof Random proof to send, or an empty string when deleting
     * @param int $expirationTime Absolute cookie expiration timestamp in Unix seconds
     * @throws \RuntimeException If PHP cannot write the cookie header
     */
    private function setBrowserProofCookie(string $cookieName, string $browserProof, int $expirationTime): void
    {
        if (!setcookie($cookieName, $browserProof, [
            "expires" => $expirationTime,
            "path" => "/",
            "secure" => true,
            "httponly" => true,
            "samesite" => "Lax",
        ])) {
            throw new \RuntimeException("Unable to write OAuth2 browser proof cookie");
        }
    }
}
