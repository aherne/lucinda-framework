<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\MVC\EventListener\UnFaceted;
use Lucinda\STDOUT\Session as SessionSTDOUT;

final class Session implements UnFaceted
{
    private SessionSTDOUT $session;

    public function __construct(SessionSTDOUT $session)
    {
        $this->session = $session;
    }

    public function run(): void
    {
        if (!$this->session->isStarted() && !$this->session->start()) {
            throw new \RuntimeException("Unable to start the session");
        }
    }
}