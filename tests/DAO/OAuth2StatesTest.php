<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;
use Test\Lucinda\Project\Support\MySqlFixture;

/** Verifies OAuth2States application behavior. */
final class OAuth2StatesTest
{
    /**
     * Requires matching provider and proof before consuming a live state once
     *
     * @return Result[] Observed behavior assertions
     */
    private function testSingleUse(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $dao = new DAO\OAuth2States($db->getExecutor());
        $proof = str_repeat('a', 64);
        $dao->save('State', 'Google', time() + 60, $proof);
        $duplicate = false;
        try {
            $dao->save('State', 'Google', time() + 60, $proof);
        } catch (\Lucinda\SQL\StatementException) {
            $duplicate = true;
        }
        $results[] = (new Validator\Booleans(
            $duplicate
        ))->assertTrue(
            'An existing state cannot be replaced'
        );
        $results[] = (new Validator\Booleans(
            $dao->consume('state', 'Google', $proof) === false
        ))->assertTrue(
            'State is case sensitive'
        );
        $results[] = (new Validator\Booleans(
            $dao->consume('State ', 'Google', $proof) === false
        ))->assertTrue(
            'State retains trailing bytes'
        );
        $results[] = (new Validator\Booleans(
            $dao->consume('State', 'GitHub', $proof) === false
        ))->assertTrue(
            'Wrong provider rejected'
        );
        $results[] = (new Validator\Booleans(
            $dao->consume('State', 'Google', str_repeat('b', 64)) === false
        ))->assertTrue(
            'Wrong browser proof rejected'
        );
        $results[] = (new Validator\Booleans(
            $dao->consume('State', 'Google', $proof) === true
        ))->assertTrue(
            'Matching live state accepted'
        );
        $results[] = (new Validator\Booleans(
            $dao->consume('State', 'Google', $proof) === false
        ))->assertTrue(
            'Consumed state cannot replay'
        );
        $dao->save('expired', 'Google', time() - 1, $proof);
        $dao->save('live', 'Google', time() + 60, $proof);
        $results[] = (new Validator\Booleans(
            $dao->consume('expired', 'Google', $proof) === false
        ))->assertTrue(
            'Expired state rejected'
        );
        $dao->save('abandoned', 'Google', time() - 1, $proof);
        $dao->clearExpired();
        $results[] = (new Validator\Arrays(
            $db->execute(
                'SELECT state FROM oauth2_states'
            )->fetchAll(\PDO::FETCH_COLUMN)
        ))->assertIdentical(
            ['live'],
            'Expiration cleanup preserves live states'
        );
        return $results;
    }

    /**
     * Rejects state creation and consumption in an application transaction
     *
     * @return Result[] Observed behavior assertions
     */
    public function testTransactionGuard(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $sql = $db->getExecutor();
        $dao = new DAO\OAuth2States($sql);
        $sql->begin();
        try {
            $rejected = false;
            try {
                $dao->save('state', 'Google', time() + 60, str_repeat('a', 64));
            } catch (\LogicException) {
                $rejected = true;
            }
            $results[] = (new Validator\Booleans(
                $rejected
            ))->assertTrue(
                'Save requires immediate persistence'
            );
            $rejected = false;
            try {
                $dao->consume('state', 'Google', str_repeat('a', 64));
            } catch (\LogicException) {
                $rejected = true;
            }
            $results[] = (new Validator\Booleans(
                $rejected
            ))->assertTrue(
                'Consume requires immediate persistence'
            );
        } finally {
            $sql->rollback();
        }
        return $results;
    }
    /**
     * Verifies save through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function save(): array
    {
        return $this->testSingleUse();
    }

    /**
     * Verifies consume through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function consume(): array
    {
        return $this->testSingleUse();
    }

    /**
     * Verifies clearExpired through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function clearExpired(): array
    {
        return $this->testSingleUse();
    }
}
