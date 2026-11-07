<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Builders\MfaBuilder;
use Lucinda\Project\Persistence\Entities\Mfa as Enrollment;
use Lucinda\Project\Persistence\Queries\Mfa\ClearSetupSecret;
use Lucinda\Project\Persistence\Queries\Mfa\ConsumeCounter;
use Lucinda\Project\Persistence\Queries\Mfa\EnableEnrollment;
use Lucinda\Project\Persistence\Queries\Mfa\EnsureEnrollment;
use Lucinda\Project\Persistence\Queries\Mfa\FindEnrollment;
use Lucinda\Project\Persistence\Queries\Mfa\SaveSetupSecret;
use Lucinda\Project\Persistence\SecretBox;
use Lucinda\Project\Persistence\SqlExecutor;
use Lucinda\WebSecurity\DAO\MultiFactorAuthentication as MfaContract;

/**
 * Coordinates account MFA policy, encrypted TOTP enrollment and replay checks
 *
 * Setup and enrolled secrets remain separate. Conditional writes preserve the
 * verified setup counter when enrollment completes and prevent counter replay.
 */
final class MultiFactorAuthentication implements MfaContract
{
    /** Executor used for account policy and enrollment operations */
    private SqlExecutor $sql;

    /** Encryption instance initialized when secret storage is first used */
    private ?SecretBox $box;

    /** Lifetime of an unconfirmed setup secret in seconds */
    private int $setupLifetime;

    /**
     * Captures persistence, encryption and pending enrollment policy
     *
     * @param SqlExecutor|null $sql Executor sharing the caller's connection
     * @param SecretBox|null $box Encryption instance owning the MFA private key
     * @param int $setupLifetime Positive setup-secret lifetime in seconds
     * @throws \InvalidArgumentException If the setup lifetime is invalid
     */
    public function __construct(
        ?SqlExecutor $sql = null,
        ?SecretBox $box = null,
        int $setupLifetime = 300
    ) {
        if ($setupLifetime < 1) {
            throw new \InvalidArgumentException('Invalid setup lifetime');
        }
        $this->sql = $sql ?? new SqlExecutor();
        $this->box = $box;
        $this->setupLifetime = $setupLifetime;
    }

    /**
     * Resolves the encryption instance used by this enrollment operation
     *
     * @return SecretBox Instance retaining the private encryption key
     * @throws \RuntimeException If the configured key is missing or invalid
     */
    private function getBox(): SecretBox
    {
        if ($this->box === null) {
            $this->box = new SecretBox();
        }
        return $this->box;
    }

    /**
     * Reads and explicitly builds one active account's enrollment projection
     *
     * @param int|string $id Authenticated local account identifier
     * @return Enrollment Internal policy and enrollment record
     * @throws \UnexpectedValueException If the active account does not exist
     * @throws \Throwable If selection or projection validation fails
     */
    private function getEnrollment(int|string $id): Enrollment
    {
        $selection = $this->sql->select(new FindEnrollment($id));
        $row = $selection->getFirstRow();
        if ($row === null) {
            throw new \UnexpectedValueException('Active MFA account not found');
        }
        $builder = new MfaBuilder($row);
        return $builder->build();
    }

    /**
     * Reads whether account policy requires completion of MFA
     *
     * @param int|string $userID Authenticated local account identifier
     * @return bool True when MFA is required independently of enrollment
     * @throws \Throwable If the active account policy cannot be read
     */
    public function isRequired(int|string $userID): bool
    {
        $enrollment = $this->getEnrollment($userID);
        return $enrollment->isRequired();
    }

    /**
     * Gets the account label used in its authenticator provisioning URI
     *
     * @param int|string $userID Authenticated local account identifier
     * @return string Account email or local identifier used as a label
     * @throws \Throwable If account policy cannot be read
     */
    public function getAccountName(int|string $userID): string
    {
        $enrollment = $this->getEnrollment($userID);
        return $enrollment->getAccountName();
    }

    /**
     * Authenticates and decrypts an enrolled TOTP secret when one exists
     *
     * @param int|string $userID Authenticated local account identifier
     * @return string|null Plaintext Base32 secret or null before enrollment
     * @throws \Throwable If selection or ciphertext authentication fails
     */
    public function getSecret(int|string $userID): ?string
    {
        $enrollment = $this->getEnrollment($userID);
        if ($enrollment->getSecret() === null) {
            return null;
        }
        return $this->getBox()->decrypt($enrollment->getSecret());
    }

    /**
     * Decrypts an unexpired setup secret without enabling enrollment
     *
     * @param int|string $userID Authenticated local account identifier
     * @return string|null Pending secret or null when absent or expired
     * @throws \Throwable If selection or ciphertext authentication fails
     */
    public function getSetupSecret(int|string $userID): ?string
    {
        $enrollment = $this->getEnrollment($userID);
        if (
            $enrollment->getSetupSecret() === null
            || ($enrollment->getSetupExpires() ?? 0) <= time()
        ) {
            return null;
        }
        return $this->getBox()->decrypt($enrollment->getSetupSecret());
    }

    /**
     * Stores a new setup secret only when no valid enrollment supersedes it
     *
     * @param int|string $userID Authenticated local account identifier
     * @param string $secret Plaintext Base32 setup secret
     * @throws \RuntimeException If enrollment already exists or changed
     * @throws \Throwable If encryption or immediate persistence fails
     */
    public function saveSetupSecret(int|string $userID, string $secret): void
    {
        $this->sql->requireAutocommit();
        $cipher = $this->getBox()->encrypt($secret);
        $this->sql->write(new EnsureEnrollment($userID));
        $now = time();
        $query = new SaveSetupSecret(
            $userID,
            $cipher,
            $now,
            $now + $this->setupLifetime
        );
        $result = $this->sql->write($query);
        if ($result->getAffectedRows() !== 1) {
            throw new \RuntimeException(
                'Enrollment already exists or changed; retry setup'
            );
        }
    }

    /**
     * Promotes a confirmed setup secret while preserving its consumed counter
     *
     * @param int|string $userID Authenticated local account identifier
     * @param string $secret Plaintext setup secret confirmed by the caller
     * @throws \RuntimeException If setup is unverified, expired or changed
     * @throws \Throwable If decryption or the conditional write fails
     */
    public function enable(int|string $userID, string $secret): void
    {
        $enrollment = $this->getEnrollment($userID);
        if ($enrollment->getSetupSecret() === null) {
            throw new \RuntimeException('Setup secret does not exist');
        }
        $expected = $this->getBox()->decrypt($enrollment->getSetupSecret());
        if (!hash_equals($expected, $secret)) {
            throw new \RuntimeException('Setup secret does not match');
        }
        $query = new EnableEnrollment(
            $userID,
            $enrollment->getSetupSecret(),
            time()
        );
        $result = $this->sql->write($query);
        if ($result->getAffectedRows() !== 1) {
            throw new \RuntimeException(
                'Enrollment expired, unverified or changed concurrently'
            );
        }
    }

    /**
     * Clears temporary setup state without deleting the enrolled secret
     *
     * @param int|string $userID Authenticated local account identifier
     * @throws \Throwable If the conditional cleanup write fails
     */
    public function clearSetupSecret(int|string $userID): void
    {
        $this->sql->write(new ClearSetupSecret($userID));
    }

    /**
     * Atomically accepts a verified counter only when it is strictly newer
     *
     * @param int|string $userID Authenticated local account identifier
     * @param int $counter Verified TOTP time-step counter
     * @return bool True only when this operation consumed the counter
     * @throws \Throwable If immediate persistence or the conditional write
     *     fails
     */
    public function consumeTotpCounter(int|string $userID, int $counter): bool
    {
        if ($counter < 0) {
            return false;
        }
        $this->sql->requireAutocommit();
        $query = new ConsumeCounter($userID, $counter, time());
        $result = $this->sql->write($query);
        return $result->getAffectedRows() === 1;
    }
}
