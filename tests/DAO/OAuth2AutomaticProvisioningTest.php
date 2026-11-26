<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;
use Test\Lucinda\Project\Support\MySqlFixture;
use Test\Lucinda\Project\Support\ProviderUser;

/** Verifies OAuth2AutomaticProvisioning application behavior. */
final class OAuth2AutomaticProvisioningTest
{
    /**
     * Creates identities idempotently and keeps matching emails separate
     *
     * @return Result[] Observed behavior assertions
     */
    private function testAccountCreation(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $dao = new DAO\OAuth2AutomaticProvisioning($db->getExecutor());
        $results[] = (new Validator\Booleans(
            $dao->create(new ProviderUser(''), 'Google') === null
        ))->assertTrue(
            'Empty remote ID rejected'
        );
        $results[] = (new Validator\Booleans(
            $dao->create(new ProviderUser('a'), '') === null
        ))->assertTrue(
            'Empty provider rejected'
        );
        $id = $dao->create(new ProviderUser('a'), 'Google');
        $results[] = (new Validator\Booleans(
            is_int($id) && $id > 0
        ))->assertTrue(
            'Provisioning returns a generated account ID'
        );
        $results[] = (new Validator\Arrays(
            [$dao->create(new ProviderUser('a'), 'Google')]
        ))->assertIdentical(
            [$id],
            'Repeated identity is idempotent'
        );
        $other = $dao->create(new ProviderUser('b'), 'Google');
        $results[] = (new Validator\Booleans(
            $other !== $id
        ))->assertTrue(
            'Matching provider emails do not merge distinct identities'
        );
        $results[] = (new Validator\Integers(
            (int) $db->execute('SELECT COUNT(*) FROM users')->fetchColumn()
        ))->assertEquals(
            2,
            'Idempotent provisioning does not create orphan accounts'
        );
        $results[] = (new Validator\Arrays(
            [$dao->resolve(new ProviderUser('a'), 'Google')]
        ))->assertIdentical(
            [$id],
            'Provisioned identity can log in'
        );
        return $results;
    }

    /**
     * Rolls back account creation when its identity link cannot be persisted
     *
     * @return Result[] Observed behavior assertions
     */
    public function testRollback(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $dao = new DAO\OAuth2AutomaticProvisioning($db->getExecutor());
        $before = (int) $db->execute(
            'SELECT COUNT(*) FROM users'
        )->fetchColumn();
        try {
            $dao->create(new ProviderUser(str_repeat('x', 256)), 'Google');
        } catch (\Lucinda\SQL\StatementException) {
            // A constraint failure may propagate or leave no winning account.
        }
        $results[] = (new Validator\Arrays(
            [(int) $db->execute('SELECT COUNT(*) FROM users')->fetchColumn()]
        ))->assertIdentical(
            [$before],
            'Failed linking leaves no account insertion behind'
        );
        $results[] = (new Validator\Booleans(
            $dao->create(
                new ProviderUser('valid'),
                'Google'
            ) !== null
        ))->assertTrue(
            'A later transaction succeeds after rollback'
        );
        return $results;
    }
    /**
     * Verifies create through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function create(): array
    {
        return $this->testAccountCreation();
    }

    /**
     * Verifies resolve through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function resolve(): array
    {
        return $this->testAccountCreation();
    }
}
