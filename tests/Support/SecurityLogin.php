<?php

namespace Test\Lucinda\Project\Support;

use Lucinda\WebSecurity\DAO\FormLogin;

/** Supplies an isolated fixed credential pair for security workflow tests. */
final class SecurityLogin implements FormLogin
{
    /**
     * Accepts only the credential pair owned by this fixture
     *
     * @param string $username Submitted username
     * @param string $password Submitted plaintext password
     * @return int|null Fixture account ID or null when rejected
     */
    public function login(string $username, string $password): ?int
    {
        return $username === 'unit' && $password === 'secret' ? 17 : null;
    }
}
