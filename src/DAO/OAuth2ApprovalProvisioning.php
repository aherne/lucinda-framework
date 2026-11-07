<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Builders\ApprovalBuilder;
use Lucinda\Project\Persistence\Queries\Approvals\FindApproval;
use Lucinda\Project\Persistence\Queries\Approvals\RequestApproval;
use Lucinda\WebSecurity\DAO\OAuth2\ApprovalProvisioning;
use Lucinda\WebSecurity\DAO\OAuth2\UserInformation;
use Lucinda\WebSecurity\OAuth2ApprovalStatus;

/**
 * Records idempotent approval requests for unlinked OAuth2 identities
 *
 * Requesting approval never creates an eligible login identity. Administrative
 * approval must explicitly create the local account and identity link.
 */
final class OAuth2ApprovalProvisioning extends OAuth2Login implements
    ApprovalProvisioning
{
    /**
     * Records or reads an approval request without replacing an existing denial
     *
     * @param UserInformation $userInformation Identity supplied by the provider
     * @param string $vendorName Configured OAuth2 provider name
     * @return OAuth2ApprovalStatus Pending or rejected approval outcome
     * @throws \Throwable If request persistence or projection validation fails
     */
    public function requestApproval(
        UserInformation $userInformation,
        string $vendorName
    ): OAuth2ApprovalStatus {
        $remote = (string) $userInformation->getId();
        if ($vendorName === '' || $remote === '') {
            return OAuth2ApprovalStatus::REJECTED;
        }
        $query = new RequestApproval(
            $vendorName,
            $remote,
            $userInformation->getName(),
            $userInformation->getEmail()
        );
        $this->sql->write($query);
        $selection = $this->sql->select(new FindApproval($vendorName, $remote));
        $row = $selection->getFirstRow();
        if ($row === null) {
            return OAuth2ApprovalStatus::REJECTED;
        }
        $builder = new ApprovalBuilder($row);
        $request = $builder->build();
        if ($request->getStatus() === 'pending') {
            return OAuth2ApprovalStatus::PENDING;
        }
        return OAuth2ApprovalStatus::REJECTED;
    }
}
