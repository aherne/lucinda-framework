<?php

namespace Test\Lucinda\Project\mocks\Authorization;

use Lucinda\WebSecurity\Authorization\DAO\PageAuthorizationDAO;

class MockPageAuthorizationDAO extends PageAuthorizationDAO
{
    protected function detectID(string $pageURL): ?int
    {
        switch ($pageURL) {
            case "login":
                return 1;
            case "index":
                return 2;
            case "administration":
                return 3;
            default:
                return null;
        }
    }

    public function isPublic(): bool
    {
        switch ($this->pageID) {
            case 1:
                return true;
            default:
                return false;
        }
    }
}
