<?php

namespace Lucinda\Project\Facets;

use Lucinda\MVC\Facet;
use Lucinda\WebSecurity\Packets\MultiFactor as MultiFactorPacket;

final class MultiFactor implements Facet
{
    private ?MultiFactorPacket $multiFactorPacket = null;

    public function setPacket(MultiFactorPacket $multiFactorPacket): void
    {
        $this->multiFactorPacket = $multiFactorPacket;
    }

    public function getPacket(): ?MultiFactorPacket
    {
        return $this->multiFactorPacket;
    }
}