<?php

namespace Lucinda\Project\Facets;

use Lucinda\MVC\Facet;

final class Security implements Facet
{
    private int|string|null $userID = null;
    private string|null $accessToken = null;
    private string|null $csrfToken = null;
    private string|null $callback = null;

    public function setUserID(int|string|null $userID): void
    {
        $this->userID = $userID;
    }

    public function getUserID(): int|string|null
    {
        return $this->userID;
    }

    public function setAccessToken(string|null $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken(): string|null
    {
        return $this->accessToken;
    }

    public function setCsrfToken(string|null $csrfToken): void
    {
        $this->csrfToken = $csrfToken;
    }

    public function getCsrfToken(): string|null
    {
        return $this->csrfToken;
    }

    public function setCallback(string $callback): void
    {
        $this->callback = $callback;
    }

    public function getCallback(): string|null
    {
        return $this->callback;
    }
}