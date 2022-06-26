<?php

namespace Test\Lucinda\Project\mocks\Authorization;

use Lucinda\WebSecurity\Authorization\DAO\PageAuthorizationDAO;
use Lucinda\WebSecurity\Authorization\DAO\UserAuthorizationDAO;

class MockUserAuthorizationDAO extends UserAuthorizationDAO
{
    public function isAllowed(PageAuthorizationDAO $page, string $httpRequestMethod): bool
    {
        if ($page->isPublic()) {
            return true;
        }
        // user id 1 only has access to page id 2
        return $this->userID==1 && $page->getID()==2;
    }
}
