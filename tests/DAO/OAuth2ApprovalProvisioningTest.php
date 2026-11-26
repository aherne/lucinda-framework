<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\WebSecurity;
use Lucinda\Project\DAO;
use Test\Lucinda\Project\Support\MySqlFixture;
use Test\Lucinda\Project\Support\ProviderUser;

/** Verifies OAuth2ApprovalProvisioning application behavior. */
final class OAuth2ApprovalProvisioningTest
{
    /**
     * Preserves administrative decisions and never provisions login accounts
     *
     * @return Result[] Observed behavior assertions
     */
    private function testApprovalLifecycle(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $dao = new DAO\OAuth2ApprovalProvisioning($db->getExecutor());
        $user = new ProviderUser('remote');
        $pending = WebSecurity\OAuth2ApprovalStatus::PENDING;
        $rejected = WebSecurity\OAuth2ApprovalStatus::REJECTED;
        $results[] = (new Validator\Arrays(
            [$dao->requestApproval($user, '')]
        ))->assertIdentical(
            [$rejected],
            'Empty provider rejected'
        );
        $results[] = (new Validator\Arrays(
            [$dao->requestApproval(new ProviderUser(''), 'Google')]
        ))->assertIdentical(
            [$rejected],
            'Empty identity rejected'
        );
        $results[] = (new Validator\Arrays(
            [$dao->requestApproval($user, 'Google')]
        ))->assertIdentical(
            [$pending],
            'New request is pending'
        );
        $results[] = (new Validator\Arrays(
            [$dao->requestApproval($user, 'Google')]
        ))->assertIdentical(
            [$pending],
            'Repeated request stays pending'
        );
        $db->execute("UPDATE oauth2_approvals SET status = 'rejected'");
        $results[] = (new Validator\Arrays(
            [$dao->requestApproval($user, 'Google')]
        ))->assertIdentical(
            [$rejected],
            'Rejection cannot be overwritten'
        );
        $results[] = (new Validator\Integers(
            (int) $db->execute(
                'SELECT COUNT(*) FROM oauth2_approvals'
            )->fetchColumn()
        ))->assertEquals(
            1,
            'Approval identity remains unique'
        );
        $results[] = (new Validator\Integers(
            (int) $db->execute('SELECT COUNT(*) FROM users')->fetchColumn()
        ))->assertEquals(
            0,
            'Requesting approval creates no login account'
        );
        return $results;
    }
    /**
     * Verifies requestApproval through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function requestApproval(): array
    {
        return $this->testApprovalLifecycle();
    }

    /**
     * Verifies resolve through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function resolve(): array
    {
        return $this->testApprovalLifecycle();
    }
}
