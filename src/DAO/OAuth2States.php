<?php

namespace Lucinda\Project\DAO;

require_once dirname(__DIR__, 2)."/helpers/SQL.php";

/**
 * Persists browser-bound OAuth2 authorization attempts in SQL
 *
 * Stores only the browser proof's SHA-256 hash. Consumption uses a conditional
 * DELETE and its affected-row count so concurrent callbacks cannot both accept
 * the same attempt. The configured SQL connection must use autocommit, and
 * these operations must run outside application transactions.
 *
 * @see \Lucinda\Project\OAuth2State
 */
final class OAuth2States
{
    /**
     * Inserts one authorization attempt without replacing an existing state
     *
     * @param string $state Full random state sent to the OAuth2 provider
     * @param string $vendorName Configured provider name bound to the attempt
     * @param int $validUntil Absolute expiration timestamp in Unix seconds
     * @param string $browserProofHash Hexadecimal SHA-256 hash of the browser proof
     * @throws \Lucinda\SQL\Exception If insertion or database access fails
     */
    public function save(string $state, string $vendorName, int $validUntil, string $browserProofHash): void
    {
        \SQL(
            "INSERT INTO oauth2_states (state, browser_proof_hash, vendor_name, valid_until)
            VALUES (:state, :browser_proof_hash, :vendor_name, :valid_until)",
            [
                ":state" => $state,
                ":browser_proof_hash" => $browserProofHash,
                ":vendor_name" => $vendorName,
                ":valid_until" => $validUntil,
            ]
        );
    }

    /**
     * Atomically accepts and deletes a live attempt matching its browser proof
     *
     * A successful deletion accepts the attempt exactly once. A rejected
     * callback also removes an expired entry belonging to the same browser,
     * but never deletes a live entry with a mismatched provider or proof.
     *
     * @param string $state State received from the provider callback
     * @param string $vendorName Configured provider handling the callback
     * @param string $browserProofHash Hexadecimal SHA-256 hash of the received cookie proof
     * @return bool True only when exactly one live matching row was deleted
     * @throws \Lucinda\SQL\Exception If deletion or database access fails
     */
    public function consume(string $state, string $vendorName, string $browserProofHash): bool
    {
        $now = time();
        $result = \SQL(
            "DELETE FROM oauth2_states
            WHERE state = :state AND vendor_name = :vendor_name
            AND browser_proof_hash = :browser_proof_hash AND valid_until > :now",
            [
                ":state" => $state,
                ":vendor_name" => $vendorName,
                ":browser_proof_hash" => $browserProofHash,
                ":now" => $now,
            ]
        );
        if ($result->getAffectedRows() === 1) {
            return true;
        }

        \SQL(
            "DELETE FROM oauth2_states
            WHERE state = :state AND browser_proof_hash = :browser_proof_hash
            AND valid_until <= :now",
            [
                ":state" => $state,
                ":browser_proof_hash" => $browserProofHash,
                ":now" => $now,
            ]
        );
        return false;
    }

    /**
     * Deletes expired attempts, including abandoned browser login flows
     *
     * Call periodically from application maintenance rather than relying on
     * callbacks to visit every expired attempt.
     *
     * @throws \Lucinda\SQL\Exception If deletion or database access fails
     */
    public function clearExpired(): void
    {
        \SQL("DELETE FROM oauth2_states WHERE valid_until <= :now", [":now" => time()]);
    }
}
