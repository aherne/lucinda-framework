<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;

/** Verifies Logout application behavior. */
final class LogoutTest
{
    /**
     * Accepts application identifiers without touching persistence
     *
     * @return Result[] Observed behavior assertions
     */
    public function logout(): array
    {
        $results = [];
        $dao = new DAO\Logout();
        $results[] = (new Validator\Booleans(
            $dao->logout(17) === true
        ))->assertTrue(
            'Integer account can log out'
        );
        $results[] = (new Validator\Booleans(
            $dao->logout('remote-account') === true
        ))->assertTrue(
            'String account can log out'
        );
        return $results;
    }
}
