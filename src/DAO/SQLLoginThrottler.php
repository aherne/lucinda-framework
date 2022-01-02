<?php
namespace Lucinda\Project\DAO;

use Lucinda\Framework\AbstractLoginThrottler;

/**
 * Login throttler that uses a "user_logins" SQL table (created beforehand) to check logins history. Create table statement if MySQL:
 *
    CREATE TABLE user_logins (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip VARCHAR(45) NOT NULL,
    username VARCHAR(255) NOT NULL,
    attempts BIGINT UNSIGNED NOT NULL default 0,
    penalty_expiration DATETIME DEFAULT NULL,
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY(id),
    UNIQUE(ip, username)
    ) Engine=InnoDB
 *
 */
class SQLLoginThrottler extends AbstractLoginThrottler
{
    public const TABLE_NAME = "user_logins";

    private bool $found = false;

    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authentication\Form\LoginThrottler::setCurrentStatus()
     */
    protected function setCurrentStatus(): void
    {
        $row = SQL("SELECT attempts, penalty_expiration FROM ".self::TABLE_NAME." WHERE ip=:ip AND username=:username", [
            ":ip"=>$this->request->getIpAddress(),
            ":username"=>$this->userName
        ])->toRow();
        if (!empty($row)) {
            $this->attempts = $row["attempts"];
            $this->penaltyExpiration = $row["penalty_expiration"];
            $this->found = true;
        }
    }

    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authentication\Form\LoginThrottler::persist()
     */
    protected function persist(): void
    {
        if (!$this->found) {
            SQL("INSERT INTO ".self::TABLE_NAME." (ip, username, attempts, penalty_expiration) VALUES (:ip, :username, :attempts, :penalty_expiration)", [
                ":ip"=>$this->request->getIpAddress(),
                ":username"=>$this->userName,
                ":attempts"=>$this->attempts,
                ":penalty_expiration"=>$this->penaltyExpiration
            ]);
        } else {
            SQL("UPDATE ".self::TABLE_NAME." SET attempts=:attempts, penalty_expiration=:penalty_expiration WHERE ip=:ip AND username=:username", [
                ":ip"=>$this->request->getIpAddress(),
                ":username"=>$this->userName,
                ":attempts"=>$this->attempts,
                ":penalty_expiration"=>$this->penaltyExpiration
            ]);
        }
    }
}
