<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;
use Test\Lucinda\Project\Support\MySqlFixture;

/** Verifies PageAuthorization application behavior. */
final class PageAuthorizationTest
{
    /**
     * Resolves exact routes and denies unknown private resources
     *
     * @return Result[] Observed behavior assertions
     */
    private function testPagePolicies(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $db->execute(
            'INSERT INTO resources (id, url, is_public) VALUES (?, ?, ?)',
            [1, 'Public', 1]
        );
        $db->execute(
            'INSERT INTO resources (id, url, is_public) VALUES (?, ?, ?)',
            [2, 'private', 0]
        );
        $dao = new DAO\PageAuthorization($db->getExecutor());
        $results[] = (new Validator\Integers(
            $dao->getID('Public')
        ))->assertEquals(
            1,
            'Persisted route resolves to its integer ID'
        );
        $results[] = (new Validator\Booleans(
            $dao->getID('public') === null
        ))->assertTrue(
            'Route lookup is case sensitive'
        );
        $results[] = (new Validator\Booleans(
            $dao->getID("' OR 1=1 --") === null
        ))->assertTrue(
            'Hostile route remains data'
        );
        $results[] = (new Validator\Booleans(
            $dao->isPublic(1) === true
        ))->assertTrue(
            'Public route permits guests'
        );
        $results[] = (new Validator\Booleans(
            $dao->isPublic(2) === false
        ))->assertTrue(
            'Private route denies guests'
        );
        $results[] = (new Validator\Booleans(
            $dao->isPublic(9999) === false
        ))->assertTrue(
            'Unknown resource denies guests'
        );
        return $results;
    }
    /**
     * Verifies getID through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getID(): array
    {
        return $this->testPagePolicies();
    }

    /**
     * Verifies isPublic through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function isPublic(): array
    {
        return $this->testPagePolicies();
    }
}
