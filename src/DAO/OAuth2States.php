<?php

namespace Lucinda\Project\DAO;

use Lucinda\Framework\OAuth2\StateStorage;
use Lucinda\Project\Persistence\Queries\States\ConsumeState;
use Lucinda\Project\Persistence\Queries\States\DeleteExpiredStates;
use Lucinda\Project\Persistence\Queries\States\SaveState;
use Lucinda\Project\Persistence\SqlExecutor;

/**
 * Persists and atomically consumes browser-bound authorization attempts
 *
 * Only the browser proof hash is persisted. Immediate autocommit and a
 * conditional delete ensure concurrent callbacks cannot accept one state twice.
 */
final class OAuth2States implements StateStorage
{
    /** Executor used for immediately committed state operations */
    private SqlExecutor $sql;

    /**
     * Captures an injected executor or a lazily configured SQL connection
     *
     * @param SqlExecutor|null $sql Executor sharing the caller's connection
     */
    public function __construct(?SqlExecutor $sql = null)
    {
        $this->sql = $sql ?? new SqlExecutor();
    }

    /**
     * Inserts one attempt without replacing a previously stored state
     *
     * @param string $state Random state sent to the OAuth2 provider
     * @param string $vendorName Provider bound to this attempt
     * @param int $validUntil Absolute expiration in Unix seconds
     * @param string $browserProofHash Hexadecimal SHA-256 browser proof hash
     * @throws \Throwable If immediate persistence or insertion fails
     */
    public function save(
        string $state,
        string $vendorName,
        int $validUntil,
        string $browserProofHash
    ): void {
        $this->sql->requireAutocommit();
        $query = new SaveState(
            $state,
            $vendorName,
            $validUntil,
            $browserProofHash
        );
        $this->sql->write($query);
    }

    /**
     * Atomically accepts and deletes one live matching authorization attempt
     *
     * @param string $state State received from the callback
     * @param string $vendorName Provider handling the callback
     * @param string $browserProofHash Hash of the received browser proof
     * @return bool True only when exactly one live matching row was deleted
     * @throws \Throwable If immediate persistence or deletion fails
     */
    public function consume(
        string $state,
        string $vendorName,
        string $browserProofHash
    ): bool {
        $this->sql->requireAutocommit();
        $now = time();
        $query = new ConsumeState(
            $state,
            $vendorName,
            $browserProofHash,
            $now
        );
        $result = $this->sql->write($query);
        if ($result->getAffectedRows() === 1) {
            return true;
        }
        $expired = new DeleteExpiredStates($now, $state, $browserProofHash);
        $this->sql->write($expired);
        return false;
    }

    /**
     * Deletes abandoned attempts after their expiration time
     *
     * @throws \Throwable If maintenance deletion fails
     */
    public function clearExpired(): void
    {
        $this->sql->write(new DeleteExpiredStates(time()));
    }
}
