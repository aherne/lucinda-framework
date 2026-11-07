<?php

namespace Lucinda\Project\Persistence;

/**
 * Identifies one failure window by scope, subject and canonical client address
 *
 * The instance owns the stable identifier shared by SQL and Redis storage.
 * Equivalent IPv6 representations resolve to the same failure window.
 *
 * @internal
 */
final class ThrottleBucket
{
    /** Hashed identity of this authentication window */
    private string $key;

    /**
     * Captures and canonicalizes one authentication subject and client address
     *
     * @param string $scope Authentication operation, such as form or mfa
     * @param string $subject Submitted username or local user identifier
     * @param string $ip Client address supplied by the request
     * @throws \JsonException If the identifier cannot be encoded
     */
    public function __construct(string $scope, string $subject, string $ip)
    {
        $packed = @inet_pton($ip);
        $address = $ip;
        if ($packed !== false) {
            $address = bin2hex($packed);
        }
        $identity = json_encode(
            [$scope, $subject, $address],
            JSON_THROW_ON_ERROR
        );
        $this->key = hash('sha256', $identity);
    }

    /**
     * Gets the canonical identifier used by the selected storage backend
     *
     * @return string Hexadecimal SHA-256 bucket identifier
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
