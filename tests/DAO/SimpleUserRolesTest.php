<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;

/** Verifies SimpleUserRoles application behavior. */
final class SimpleUserRolesTest
{
    /**
     * Distinguishes guests from authenticated identifiers including zero
     *
     * @return Result[] Observed behavior assertions
     */
    public function getRoles(): array
    {
        $results = [];
        $dao = new DAO\SimpleUserRoles();
        $results[] = (new Validator\Arrays(
            $dao->getRoles(null)
        ))->assertIdentical(
            ['GUESTS'],
            'Null account is a guest'
        );
        foreach ([0, 17, '0', 'account'] as $id) {
            $results[] = (new Validator\Arrays(
                $dao->getRoles($id)
            ))->assertIdentical(
                ['MEMBERS'],
                'Non-null account is a member'
            );
        }
        return $results;
    }
}
