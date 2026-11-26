<?php

namespace Lucinda\Project\Facets;

use Lucinda\MVC\Facet;
use Lucinda\WebSecurity\Packets\MultiFactor as MultiFactorPacket;

/**
 * Publishes an actionable MFA outcome for the current request
 *
 * The security listener sets a packet when the requested route should render an
 * enrollment or challenge form. Other requests retain an empty facet.
 */
final class MultiFactor implements Facet
{
    /**
     * MFA form outcome, or null when this route has none
     */
    private ?MultiFactorPacket $multiFactorPacket = null;

    /**
     * Sets the MFA outcome to present on the current route
     *
     * @param MultiFactorPacket $multiFactorPacket Enrollment or challenge
     * outcome
     * @return void
     */
    public function setPacket(MultiFactorPacket $multiFactorPacket): void
    {
        $this->multiFactorPacket = $multiFactorPacket;
    }

    /**
     * Gets the MFA form outcome selected by security processing
     *
     * @return MultiFactorPacket|null Selected form outcome, or null when absent
     */
    public function getPacket(): ?MultiFactorPacket
    {
        return $this->multiFactorPacket;
    }
}
