<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;
use Test\Lucinda\Project\Support\MySqlFixture;

/** Verifies UserRoles application behavior. */
final class UserRolesTest
{
    /**
     * Returns outward role names and excludes disabled accounts
     *
     * @return Result[] Observed behavior assertions
     */
    public function getRoles(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $id = $db->createUser();
        $dao = new DAO\UserRoles($db->getExecutor());
        $results[] = (new Validator\Arrays(
            $dao->getRoles(null)
        ))->assertIdentical(
            ['GUESTS'],
            'Guests receive the guest role'
        );
        $results[] = (new Validator\Arrays(
            $dao->getRoles($id)
        ))->assertIdentical(
            [],
            'Unassigned accounts receive no database roles'
        );
        $db->execute("INSERT INTO roles (id, name) VALUES (1, 'EDITORS')");
        $db->execute('INSERT INTO users_roles VALUES (?, ?)', [$id, 1]);
        $results[] = (new Validator\Arrays(
            $dao->getRoles((string) $id)
        ))->assertIdentical(
            ['EDITORS'],
            'Role collection contains names'
        );
        $db->execute('UPDATE users SET is_active = 0 WHERE id = ?', [$id]);
        $results[] = (new Validator\Arrays(
            $dao->getRoles($id)
        ))->assertIdentical(
            [],
            'Disabled accounts have no database roles'
        );
        return $results;
    }
}
