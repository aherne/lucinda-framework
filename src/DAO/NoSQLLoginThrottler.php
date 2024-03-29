<?php

namespace Lucinda\Project\DAO;

use Lucinda\NoSQL\Driver;
use Lucinda\WebSecurity\Request;
use Lucinda\Framework\AbstractLoginThrottler;

/**
 * Extension of BasicLoginThrottler that uses a NoSQL database as storage medium
 */
class NoSQLLoginThrottler extends AbstractLoginThrottler
{
    public const DRIVER_NAME = "";
    public const EXPIRATION = 3600;

    private string $key;
    private Driver $connection;

    /**
     * Registers variables, calculates key to search for, and checks current throttling status for request.
     *
     * @param Request $request
     * @param string  $userName Username client tries to login with.
     */
    public function __construct(Request $request, string $userName)
    {
        $this->key = "logins__".sha1(
            (string) json_encode(
                [
                "ip"=>$request->getIpAddress(),
                "username"=>$userName
                ]
            )
        );
        $this->connection = \NoSQL(self::DRIVER_NAME);
        parent::__construct($request, $userName);
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\WebSecurity\Authentication\Form\LoginThrottler::setCurrentStatus()
     */
    protected function setCurrentStatus(): void
    {
        if ($this->connection->contains($this->key)) {
            $value = $this->connection->get($this->key);
            if (!$value) {
                $this->connection->delete($this->key);
                return;
            }

            $row = json_decode($value, true);
            $this->attempts = $row["attempts"];
            $this->penaltyExpiration = $row["penalty_expiration"];
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\WebSecurity\Authentication\Form\LoginThrottler::persist()
     */
    protected function persist(): void
    {
        $this->connection->set(
            $this->key,
            json_encode(
                array(
                "attempts"=>$this->attempts,
                "penalty_expiration"=>$this->penaltyExpiration
                ),
                self::EXPIRATION
            )
        );
    }
}
