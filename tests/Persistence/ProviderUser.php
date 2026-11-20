<?php

namespace Test\Lucinda\Project\Persistence;

use Lucinda\WebSecurity\DAO\OAuth2\UserInformation;

/** Provider identity retained by the OAuth2 integration fixture. */
final class ProviderUser implements UserInformation
{
    /**
     * Captures the remote identifier supplied to this fixture
     *
     * @param string $remote Provider-assigned identity identifier
     */
    public function __construct(private string $remote)
    {
    }

    /** @return int|string Remote identifier owned by this fixture */
    public function getId(): int|string
    {
        return $this->remote;
    }

    /** @return string Display name used by account provisioning */
    public function getName(): string
    {
        return 'Integration User';
    }

    /** @return string Shared profile email used to test identity isolation */
    public function getEmail(): string
    {
        return 'same-profile@example.test';
    }
}
