<?php

namespace Test\Lucinda\Project\mocks\Authentication;

use Lucinda\Framework\OAuth2\UserDAO;
use Lucinda\WebSecurity\Authentication\OAuth2\UserInformation;
use Lucinda\WebSecurity\Authentication\OAuth2\VendorAuthenticationDAO;
use Lucinda\WebSecurity\Authorization\UserRoles;

class MockVendorAuthenticationDAO implements VendorAuthenticationDAO, UserRoles, UserDAO
{
    private $accounts = [];

    public function login(UserInformation $userInformation, string $vendorName, string $accessToken): int|string|null
    {
        if ($vendorName!="Facebook") {
            return null;
        }
        $this->accounts[1][$vendorName] = [
            "info"=>$userInformation,
            "access_token"=>$accessToken
        ];
        return 1;
    }

    public function logout($userID): void
    {
        if (isset($this->accounts[$userID])) {
            foreach ($this->accounts[$userID] as $vendorName=>$info) {
                $this->accounts[$userID][$vendorName]["access_token"] = "";
            }
        }
    }

    public function getRoles($userID): array
    {
        if ($userID) {
            return ["USER"];
        } else {
            return ["GUEST"];
        }
    }

    public function getAccessToken($userID): ?string
    {
        return "querty";
    }

    public function getVendor($userID): ?string
    {
        return "facebook";
    }
}
