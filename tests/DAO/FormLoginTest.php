<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;
use Test\Lucinda\Project\Support\MySqlFixture;

/** Verifies FormLogin application behavior. */
final class FormLoginTest
{
    /**
     * Accepts only active accounts and the exact credential pair
     *
     * @return Result[] Observed behavior assertions
     */
    public function login(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $id = $db->createUser();
        $hash = password_hash('correct', PASSWORD_BCRYPT, ['cost' => 4]);
        $db->execute(
            'INSERT INTO user_credentials VALUES (?, ?, ?)',
            [$id, 'Alice', $hash]
        );
        $dao = new DAO\FormLogin($db->getExecutor());
        $results[] = (new Validator\Booleans(
            $dao->login('missing', 'correct') === null
        ))->assertTrue(
            'Unknown account rejected'
        );
        $results[] = (new Validator\Booleans(
            $dao->login('Alice', 'wrong') === null
        ))->assertTrue(
            'Wrong password rejected'
        );
        $results[] = (new Validator\Booleans(
            $dao->login('alice', 'correct') === null
        ))->assertTrue(
            'Usernames remain case sensitive'
        );
        $results[] = (new Validator\Booleans(
            $dao->login("Alice' OR 1=1 --", 'correct') === null
        ))->assertTrue(
            'SQL injection cannot select another account'
        );
        $results[] = (new Validator\Arrays(
            [$dao->login('Alice', 'correct')]
        ))->assertIdentical(
            [$id],
            'Valid credentials return the account ID'
        );
        $updated = $db->execute(
            'SELECT password_hash FROM user_credentials'
        )->fetchColumn();
        $results[] = (new Validator\Booleans(
            $updated !== $hash && password_verify(
                'correct',
                $updated
            )
        ))->assertTrue(
            'Successful login upgrades an obsolete password verifier'
        );
        $db->execute('UPDATE users SET is_active = 0 WHERE id = ?', [$id]);
        $results[] = (new Validator\Booleans(
            $dao->login('Alice', 'correct') === null
        ))->assertTrue(
            'Disabled account rejected'
        );
        return $results;
    }
}
