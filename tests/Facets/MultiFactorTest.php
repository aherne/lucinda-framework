<?php

namespace Test\Lucinda\Project\Facets;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Facets;
use Lucinda\WebSecurity;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies MultiFactor presentation behavior. */
final class MultiFactorTest
{
    /**
     * Preserves the selected enrollment packet and its sensitive form values
     *
     * @return Result[] Observed behavior assertions
     */
    private function testFormPacket(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $facet = new Facets\MultiFactor();
        $results[] = (new Validator\Booleans(
            $facet->getPacket() === null
        ))->assertTrue(
            'No form outcome initially'
        );
        $packet = new WebSecurity\Packets\MultiFactor();
        $packet->setSecret('JBSWY3DPEHPK3PXP');
        $packet->setProvisioningURI('otpauth://totp/example');
        $facet->setPacket($packet);
        $results[] = (new Validator\Arrays(
            [$facet->getPacket()]
        ))->assertIdentical(
            [$packet],
            'Form packet is shared without copying'
        );
        $results[] = (new Validator\Strings(
            $facet->getPacket()->getSecret()
        ))->assertEquals(
            'JBSWY3DPEHPK3PXP',
            'Setup form retains its secret'
        );
        $results[] = (new Validator\Booleans(
            (new Facets\MultiFactor())->getPacket() === null
        ))->assertTrue(
            'Setup data remains local to the current request'
        );
        return $results;
    }
    /**
     * Verifies setPacket through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function setPacket(): array
    {
        return $this->testFormPacket();
    }

    /**
     * Verifies getPacket through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getPacket(): array
    {
        return $this->testFormPacket();
    }
}
