<?php

namespace Test\Lucinda\Project\Support;

use Lucinda\WebSecurity\DAO\OAuth2\UserInformation;

/** Provider identity retained by the OAuth2 integration fixture. */
final class ProviderUser implements UserInformation
{
    /** Provider-assigned identifier retained for the provisioning lifecycle */
    private string $remote;

    /**
     * Captures the remote identifier supplied to this fixture
     *
     * @param string $remote Provider-assigned identity identifier
     */
    public function __construct(string $remote)
    {
        $this->remote = $remote;
    }

    /**
     * Gets the stable provider identity used by persistence lookups
     *
     * @return int|string Remote identifier owned by this fixture
     */
    public function getId(): int|string
    {
        return $this->remote;
    }

    /**
     * Gets the display name copied into a newly provisioned account
     *
     * @return string Display name used by account provisioning
     */
    public function getName(): string
    {
        return 'Integration User';
    }

    /**
     * Gets a shared email that must never merge distinct provider identities
     *
     * @return string Shared profile email used to test identity isolation
     */
    public function getEmail(): string
    {
        return 'same-profile@example.test';
    }
}
