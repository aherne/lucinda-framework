<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Framework\Persistence;
use Lucinda\Project\DAO;
use Test\Lucinda\Project\Support\MySqlFixture;

/** Verifies MultiFactorAuthentication application behavior. */
final class MultiFactorAuthenticationTest
{
    /**
     * Keeps setup separate, encrypts secrets and prevents counter replay
     *
     * @return Result[] Observed behavior assertions
     */
    private function testEnrollment(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $id = $db->createUser(true);
        $box = new Persistence\SecretBox(base64_encode(random_bytes(32)));
        $dao = new DAO\MultiFactorAuthentication($db->getExecutor(), $box);
        $results[] = (new Validator\Booleans(
            $dao->isRequired($id) === true
        ))->assertTrue(
            'Account policy requires MFA before enrollment'
        );
        $results[] = (new Validator\Arrays(
            [$dao->getAccountName($id)]
        ))->assertIdentical(
            [(string) $id],
            'Missing email falls back to the account ID'
        );
        $results[] = (new Validator\Booleans(
            $dao->getSecret($id) === null
        ))->assertTrue(
            'No enrolled secret initially'
        );
        $results[] = (new Validator\Booleans(
            $dao->getSetupSecret($id) === null
        ))->assertTrue(
            'No setup secret initially'
        );
        $results[] = (new Validator\Booleans(
            $dao->consumeTotpCounter($id, -1) === false
        ))->assertTrue(
            'Negative counter rejected'
        );
        $results[] = (new Validator\Booleans(
            $dao->consumeTotpCounter($id, 1) === false
        ))->assertTrue(
            'Unenrolled account has no usable counter'
        );
        $dao->saveSetupSecret($id, 'JBSWY3DPEHPK3PXP');
        $results[] = (new Validator\Strings(
            $dao->getSetupSecret($id)
        ))->assertEquals(
            'JBSWY3DPEHPK3PXP',
            'Pending setup secret decrypts'
        );
        $stored = $db->execute(
            'SELECT setup_secret FROM user_mfa'
        )->fetchColumn();
        $results[] = (new Validator\Booleans(
            !str_contains($stored, 'JBSWY3DPEHPK3PXP')
        ))->assertTrue(
            'Stored setup secret is encrypted'
        );
        $rejected = false;
        try {
            $dao->enable($id, 'JBSWY3DPEHPK3PXP');
        } catch (\RuntimeException) {
            $rejected = true;
        }
        $results[] = (new Validator\Booleans(
            $rejected
        ))->assertTrue(
            'Enrollment cannot complete before a counter is verified'
        );
        $results[] = (new Validator\Booleans(
            $dao->consumeTotpCounter($id, 100) === true
        ))->assertTrue(
            'Verified setup counter accepted'
        );
        $results[] = (new Validator\Booleans(
            $dao->consumeTotpCounter($id, 100) === false
        ))->assertTrue(
            'Setup counter cannot replay'
        );
        $rejected = false;
        try {
            $dao->enable($id, 'WRONGSECRET');
        } catch (\RuntimeException) {
            $rejected = true;
        }
        $results[] = (new Validator\Booleans(
            $rejected
        ))->assertTrue(
            'Mismatched setup secret rejected'
        );
        $dao->enable($id, 'JBSWY3DPEHPK3PXP');
        $dao->clearSetupSecret($id);
        $results[] = (new Validator\Strings(
            $dao->getSecret($id)
        ))->assertEquals(
            'JBSWY3DPEHPK3PXP',
            'Confirmed secret decrypts after setup cleanup'
        );
        $results[] = (new Validator\Booleans(
            $dao->getSetupSecret($id) === null
        ))->assertTrue(
            'Setup cleanup removes pending state'
        );
        $results[] = (new Validator\Booleans(
            $dao->consumeTotpCounter($id, 99) === false
        ))->assertTrue(
            'Older counter rejected'
        );
        $results[] = (new Validator\Booleans(
            $dao->consumeTotpCounter($id, 100) === false
        ))->assertTrue(
            'Confirmation counter remains consumed'
        );
        $results[] = (new Validator\Booleans(
            $dao->consumeTotpCounter($id, 101) === true
        ))->assertTrue(
            'Strictly newer counter accepted'
        );
        return $results;
    }

    /**
     * Rejects expired setup while allowing a fresh replacement
     *
     * @return Result[] Observed behavior assertions
     */
    public function testExpiredSetup(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $id = $db->createUser();
        $box = new Persistence\SecretBox(base64_encode(random_bytes(32)));
        $dao = new DAO\MultiFactorAuthentication($db->getExecutor(), $box);
        $dao->saveSetupSecret($id, 'OLD');
        $db->execute('UPDATE user_mfa SET setup_expires = ?', [time() - 1]);
        $results[] = (new Validator\Booleans(
            $dao->getSetupSecret($id) === null
        ))->assertTrue(
            'Expired setup is not exposed'
        );
        $results[] = (new Validator\Booleans(
            $dao->consumeTotpCounter($id, 1) === false
        ))->assertTrue(
            'Expired setup cannot consume counters'
        );
        $dao->saveSetupSecret($id, 'NEW');
        $results[] = (new Validator\Strings(
            $dao->getSetupSecret($id)
        ))->assertEquals(
            'NEW',
            'Expired setup may be replaced'
        );
        $rejected = false;
        try {
            new DAO\MultiFactorAuthentication($db->getExecutor(), $box, 0);
        } catch (\InvalidArgumentException) {
            $rejected = true;
        }
        $results[] = (new Validator\Booleans(
            $rejected
        ))->assertTrue(
            'Nonpositive setup lifetime rejected'
        );
        $rejected = false;
        try {
            $dao->isRequired(9999);
        } catch (\UnexpectedValueException) {
            $rejected = true;
        }
        $results[] = (new Validator\Booleans(
            $rejected
        ))->assertTrue(
            'Missing active account fails explicitly'
        );
        return $results;
    }
    /**
     * Verifies isRequired through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function isRequired(): array
    {
        return $this->testEnrollment();
    }

    /**
     * Verifies getAccountName through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getAccountName(): array
    {
        return $this->testEnrollment();
    }

    /**
     * Verifies getSecret through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getSecret(): array
    {
        return $this->testEnrollment();
    }

    /**
     * Verifies getSetupSecret through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getSetupSecret(): array
    {
        return $this->testEnrollment();
    }

    /**
     * Verifies saveSetupSecret through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function saveSetupSecret(): array
    {
        return $this->testEnrollment();
    }

    /**
     * Verifies enable through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function enable(): array
    {
        return $this->testEnrollment();
    }

    /**
     * Verifies clearSetupSecret through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function clearSetupSecret(): array
    {
        return $this->testEnrollment();
    }

    /**
     * Verifies consumeTotpCounter through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function consumeTotpCounter(): array
    {
        return $this->testEnrollment();
    }
}
