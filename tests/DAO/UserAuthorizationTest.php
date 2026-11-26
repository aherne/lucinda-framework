<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;
use Test\Lucinda\Project\Support\MySqlFixture;

/** Verifies UserAuthorization application behavior. */
final class UserAuthorizationTest
{
    /**
     * Enforces account, resource, method and active-account restrictions
     *
     * @return Result[] Observed behavior assertions
     */
    public function isAllowed(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $id = $db->createUser();
        $db->execute("INSERT INTO resources (id, url) VALUES (1, 'private')");

        $db->execute(
            'INSERT INTO users_resources VALUES (?, ?, ?)',
            [$id, 1, 'GET']
        );
        $dao = new DAO\UserAuthorization($db->getExecutor());
        $results[] = (new Validator\Booleans(
            $dao->isAllowed($id, 1, 'GET') === true
        ))->assertTrue(
            'Explicit method grant accepted'
        );
        $results[] = (new Validator\Booleans(
            $dao->isAllowed($id, 1, 'POST') === false
        ))->assertTrue(
            'Other methods denied'
        );
        $results[] = (new Validator\Booleans(
            $dao->isAllowed($id, 2, 'GET') === false
        ))->assertTrue(
            'Unknown resources denied'
        );
        $results[] = (new Validator\Booleans(
            $dao->isAllowed($id + 1, 1, 'GET') === false
        ))->assertTrue(
            'Other accounts denied'
        );
        $db->execute('UPDATE users_resources SET http_method = ?', ['*']);
        $results[] = (new Validator\Booleans(
            $dao->isAllowed($id, 1, 'DELETE') === true
        ))->assertTrue(
            'Wildcard method grant accepted'
        );
        $db->execute('UPDATE users SET is_active = 0 WHERE id = ?', [$id]);
        $results[] = (new Validator\Booleans(
            $dao->isAllowed($id, 1, 'GET') === false
        ))->assertTrue(
            'Disabled accounts denied'
        );
        return $results;
    }
}
