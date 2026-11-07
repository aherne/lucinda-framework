<?php

namespace Lucinda\Project\Persistence\Entities;

/**
 * Account policy and optional TOTP enrollment state
 *
 * Internal immutable persistence data; never pass this entity to presentation.
 *
 * @internal
 */
final readonly class Mfa
{
    /** Whether account policy requires completing MFA */
    private bool $required;

    /** Account label used for authenticator provisioning */
    private string $accountName;

    /** Encrypted enrolled secret, absent before enrollment */
    private ?string $secret;

    /** Encrypted temporary secret awaiting confirmation */
    private ?string $setupSecret;

    /** Expiration of the temporary secret in Unix seconds */
    private ?int $setupExpires;

    /** Last verified TOTP counter consumed for this account */
    private ?int $counter;

    /**
     * Captures the validated values for this persistence record
     *
     * @param bool $required Whether account policy requires completing MFA
     * @param string $accountName Account label used for authenticator
     *     provisioning
     * @param ?string $secret Encrypted enrolled secret, absent before
     *     enrollment
     * @param ?string $setupSecret Encrypted temporary secret awaiting
     *     confirmation
     * @param ?int $setupExpires Expiration of the temporary secret in Unix
     *     seconds
     * @param ?int $counter Last verified TOTP counter consumed for this account
     */
    public function __construct(
        bool $required,
        string $accountName,
        ?string $secret,
        ?string $setupSecret,
        ?int $setupExpires,
        ?int $counter
    ) {
        $this->required = $required;
        $this->accountName = $accountName;
        $this->secret = $secret;
        $this->setupSecret = $setupSecret;
        $this->setupExpires = $setupExpires;
        $this->counter = $counter;
    }
    /**
     * Checks whether account policy requires MFA
     *
     * @return bool Whether account policy requires completing MFA
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Gets the account label used for provisioning
     *
     * @return string Account label used for authenticator provisioning
     */
    public function getAccountName(): string
    {
        return $this->accountName;
    }

    /**
     * Gets the encrypted enrolled TOTP secret
     *
     * @return ?string Encrypted enrolled secret, absent before enrollment
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * Gets the encrypted temporary setup secret
     *
     * @return ?string Encrypted temporary secret awaiting confirmation
     */
    public function getSetupSecret(): ?string
    {
        return $this->setupSecret;
    }

    /**
     * Gets the setup secret expiration timestamp
     *
     * @return ?int Expiration of the temporary secret in Unix seconds
     */
    public function getSetupExpires(): ?int
    {
        return $this->setupExpires;
    }

    /**
     * Gets the last consumed TOTP time-step counter
     *
     * @return ?int Last verified TOTP counter consumed for this account
     */
    public function getCounter(): ?int
    {
        return $this->counter;
    }
}
