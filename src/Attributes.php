<?php
namespace Lucinda\Project;

use Lucinda\Headers\Wrapper;
use Lucinda\Logging\Logger;

/**
 * Implements \Lucinda\STDOUT\Attributes set by Lucinda Framework 3.0 event listeners. Developers can add more!
 */
class Attributes extends \Lucinda\STDOUT\Attributes
{
    private ?Wrapper $headers = null;
    private ?Logger $logger = null;
    private string|int|null $userID = null;
    private ?string $csrfToken = null;
    private ?string $accessToken = null;

    /**
     * Sets pointer to query HTTP headers with
     *
     * @param Wrapper $wrapper
     */
    public function setHeaders(Wrapper $wrapper): void
    {
        $this->headers = $wrapper;
    }

    /**
     * Gets pointer to query HTTP headers with
     *
     * @return Wrapper|NULL
     */
    public function getHeaders(): ?Wrapper
    {
        return $this->headers;
    }

    /**
     * Sets pointer to log messages with
     *
     * @param Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Gets pointer to log messages with
     *
     * @return Logger|NULL
     */
    public function getLogger(): ?Logger
    {
        return $this->logger;
    }

    /**
     * Sets logged in user id
     *
     * @param int|string|null $userID
     */
    public function setUserId(int|string|null $userID): void
    {
        $this->userID = $userID;
    }

    /**
     * Gets logged in user id
     *
     * @return string|integer|null
     */
    public function getUserId(): int|string|null
    {
        return $this->userID;
    }

    /**
     * Sets token to sign logins with in order to fight Cross-Site-Requests-Forgery
     *
     * @param string $token
     */
    public function setCsrfToken(string $token): void
    {
        $this->csrfToken = $token;
    }

    /**
     * Gets token to sign logins with in order to fight Cross-Site-Requests-Forgery
     *
     * @return string|NULL
     */
    public function getCsrfToken(): ?string
    {
        return $this->csrfToken;
    }

    /**
     * Sets token to be presented by client for stateless authentication as header:
     * Authorization Bearer VALUE
     *
     * @param string|NULL $token
     */
    public function setAccessToken(?string $token): void
    {
        $this->accessToken = $token;
    }

    /**
     * Gets token to be presented by client for stateless authentication as header:
     * Authorization Bearer VALUE
     *
     * @return string|NULL
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }
}
