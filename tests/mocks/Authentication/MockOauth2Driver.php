<?php
namespace Test\Lucinda\Project\mocks\Authentication;

use Lucinda\WebSecurity\Authentication\OAuth2\Driver;
use Lucinda\WebSecurity\Authentication\OAuth2\UserInformation;

class MockOauth2Driver implements Driver
{
    private $vendorName;
    
    public function __construct(string $vendorName)
    {
        $this->vendorName = $vendorName;
    }
    
    
    public function getUserInformation(string $accessToken): UserInformation
    {
        return new MockUserInformation(["id"=>123456, "name"=>"John Doe", "email"=>"john@doe.com"]);
    }
    
    public function getCallbackUrl(): string
    {
        return "login/facebook";
    }
    
    public function getAuthorizationCode(string $scope): string
    {
        return "qwerty";
    }
    
    public function getAccessToken(string $authorizationCode): string
    {
        return "asdfgh";
    }
    
    public function getVendorName(): string
    {
        return $this->vendorName;
    }
}
