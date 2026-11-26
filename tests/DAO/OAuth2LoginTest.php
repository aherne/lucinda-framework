<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;
use Test\Lucinda\Project\Support\MySqlFixture;
use Test\Lucinda\Project\Support\ProviderUser;

/** Verifies OAuth2Login application behavior. */
final class OAuth2LoginTest
{
    /**
     * Looks up exact active provider identities without creating accounts
     *
     * @return Result[] Observed behavior assertions
     */
    public function resolve(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $id = $db->createUser();
        $dao = new DAO\OAuth2Login($db->getExecutor());
        $user = new ProviderUser('Remote');
        $results[] = (new Validator\Booleans(
            $dao->resolve($user, 'Google') === null
        ))->assertTrue(
            'Unlinked identity stays unresolved'
        );
        $results[] = (new Validator\Booleans(
            $dao->resolve($user, '') === null
        ))->assertTrue(
            'Empty provider is rejected'
        );
        $results[] = (new Validator\Booleans(
            $dao->resolve(new ProviderUser(''), 'Google') === null
        ))->assertTrue(
            'Empty remote ID rejected'
        );
        $db->execute(
            'INSERT INTO oauth2_identities (user_id, vendor_name, remote_id) VALUES (?, ?, ?)',
            [$id, 'Google', 'Remote']
        );
        $results[] = (new Validator\Arrays(
            [$dao->resolve($user, 'Google')]
        ))->assertIdentical(
            [$id],
            'Known identity resolves'
        );
        $results[] = (new Validator\Booleans(
            $dao->resolve($user, 'GitHub') === null
        ))->assertTrue(
            'Providers remain separate'
        );
        $results[] = (new Validator\Booleans(
            $dao->resolve(new ProviderUser('remote'), 'Google') === null
        ))->assertTrue(
            'Remote IDs are case sensitive'
        );
        $db->execute('UPDATE users SET is_active = 0 WHERE id = ?', [$id]);
        $results[] = (new Validator\Booleans(
            $dao->resolve($user, 'Google') === null
        ))->assertTrue(
            'Disabled identity rejected'
        );
        $results[] = (new Validator\Integers(
            (int) $db->execute('SELECT COUNT(*) FROM users')->fetchColumn()
        ))->assertEquals(
            1,
            'Identity lookup creates no accounts'
        );
        return $results;
    }
}
