<?php

namespace Test\Lucinda\Project\Support;

use Lucinda\WebSecurity\DAO\UserAuthorization;

/** Supplies a fixed private-page grant for the security workflow fixture. */
final class SecurityPermissions implements UserAuthorization
{
    /**
     * Grants only the fixture account's GET access to its private page
     *
     * @param int|string $userID Authenticated fixture account
     * @param int $pageID Requested fixture page
     * @param string $httpRequestMethod HTTP method being authorized
     * @return bool Whether the fixture grant matches
     */
    public function isAllowed(
        int|string $userID,
        int $pageID,
        string $httpRequestMethod
    ): bool {
        return $userID === 17 && $pageID === 2 && $httpRequestMethod === 'GET';
    }
}
