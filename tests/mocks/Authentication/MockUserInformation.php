<?php
namespace Test\Lucinda\Project\mocks\Authentication;

use Lucinda\WebSecurity\Authentication\OAuth2\UserInformation;

class MockUserInformation implements UserInformation
{
    private $id;
    private $name;
    private $email;

    public function __construct(array $info)
    {
        $this->id = $info["id"];
        $this->name = $info["name"];
        $this->email = $info["email"];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
