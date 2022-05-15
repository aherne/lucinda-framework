<?php

namespace Test\Lucinda\Project\mocks\Authorization;

use Lucinda\WebSecurity\Authorization\UserRoles;

class MockUserRolesDAO implements UserRoles
{
    public function getRoles($userID): array
    {
        if ($userID) {
            return ["USER"];
        } else {
            return ["GUEST"];
        }
    }
}
