<?php

namespace Lucinda\Project\Facets;

use Lucinda\MVC\Facet;

/**
 * Publishes the current request's public security context
 *
 * The security listener fills this initially anonymous facet from its outcome.
 * Consumers receive application IDs and tokens rather than persistence records.
 */
final class Security implements Facet
{
    /**
     * Authenticated application account ID, or null for a guest
     */
    private int|string|null $userID = null;
    /**
     * Bearer persistence token exposed by the security outcome, if any
     */
    private string|null $accessToken = null;
    /**
     * CSRF token bound to the current security context, if available
     */
    private string|null $csrfToken = null;
    /**
     * Navigation target exposed to a REST client, or null when absent
     */
    private string|null $callback = null;

    /**
     * Sets the authenticated account identity for this request
     *
     * @param int|string|null $userID Account ID, or null to represent a guest
     * @return void
     */
    public function setUserID(int|string|null $userID): void
    {
        $this->userID = $userID;
    }

    /**
     * Gets the authenticated account identity
     *
     * @return int|string|null Account ID, or null for a guest
     */
    public function getUserID(): int|string|null
    {
        return $this->userID;
    }

    /**
     * Sets the bearer token returned by security processing
     *
     * @param string|null $accessToken Persistence token, or null when absent
     * @return void
     */
    public function setAccessToken(string|null $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Gets the bearer token made available to the caller
     *
     * @return string|null Persistence token, or null when unavailable
     */
    public function getAccessToken(): string|null
    {
        return $this->accessToken;
    }

    /**
     * Sets the CSRF token for the current security context
     *
     * @param string|null $csrfToken CSRF token, or null when unavailable
     * @return void
     */
    public function setCsrfToken(string|null $csrfToken): void
    {
        $this->csrfToken = $csrfToken;
    }

    /**
     * Gets the CSRF token exposed by security processing
     *
     * @return string|null CSRF token, or null when unavailable
     */
    public function getCsrfToken(): string|null
    {
        return $this->csrfToken;
    }

    /**
     * Sets the navigation target to expose to a REST client
     *
     * @param string $callback Destination supplied by the security outcome
     * @return void
     */
    public function setCallback(string $callback): void
    {
        $this->callback = $callback;
    }

    /**
     * Gets the navigation target exposed to a REST client
     *
     * @return string|null Callback destination, or null when none was selected
     */
    public function getCallback(): string|null
    {
        return $this->callback;
    }
}
