<?php

namespace Test\Lucinda\Project\Support;

use Lucinda\WebSecurity\DAO\PageAuthorization;

/** Models one public and one private page for security listener tests. */
final class SecurityPages implements PageAuthorization
{
    /**
     * Resolves only the two page identities owned by this fixture
     *
     * @param string $pageURL Validated application route
     * @return int|null Fixture page ID or null for an unknown route
     */
    public function getID(string $pageURL): ?int
    {
        return match ($pageURL) {
            'catalog' => 1,
            'private' => 2,
            default => null,
        };
    }

    /**
     * Identifies the fixture's public catalog page
     *
     * @param int $pageID Fixture page ID
     * @return bool Whether guests may access this page
     */
    public function isPublic(int $pageID): bool
    {
        return $pageID === 1;
    }
}
