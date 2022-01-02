<?php
namespace Test\Lucinda\Project\mocks\Authentication;

use Lucinda\WebSecurity\Authentication\DAO\UserAuthenticationDAO;
use Lucinda\WebSecurity\Authorization\UserRoles;

class MockUsersAuthentication implements UserAuthenticationDAO, UserRoles
{
    public function login(string $username, string $password): int|string|null
    {
        return ($username=="test" && $password=="me" ? 1 : null);
    }

    public function logout($userID): void
    {
    }

    public function getRoles($userID): array
    {
        if ($userID) {
            return ["USER"];
        } else {
            return ["GUEST"];
        }
    }
};
