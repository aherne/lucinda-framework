<?php

namespace Lucinda\Project\Persistence;

/**
 * Encrypts and authenticates MFA secrets using one instance-owned private key
 *
 * The key is injected or loaded from MFA_ENCRYPTION_KEY as base64 of 32 bytes.
 * Ciphertexts include a fresh nonce and must authenticate before plaintext is
 * returned to the MFA operation.
 *
 * @internal
 */
final class SecretBox
{
    /** Secretbox key retained separately from persisted ciphertext */
    private string $key;

    /**
     * Captures the encryption key used by this MFA storage operation
     *
     * @param string|null $encodedKey Explicit base64 key or environment setting
     * @throws \RuntimeException If the key is missing or has an invalid length
     */
    public function __construct(?string $encodedKey = null)
    {
        if ($encodedKey === null) {
            $encodedKey = getenv('MFA_ENCRYPTION_KEY');
            if ($encodedKey === false) {
                $encodedKey = '';
            }
        }
        $key = base64_decode($encodedKey, true);
        if ($key === false || strlen($key) !== 32) {
            throw new \RuntimeException(
                'MFA_ENCRYPTION_KEY must encode exactly 32 private bytes'
            );
        }
        $this->key = $key;
    }

    /**
     * Encrypts one TOTP secret using a freshly generated nonce
     *
     * @param string $secret Plaintext Base32 TOTP secret
     * @return string Base64 nonce and authenticated ciphertext
     * @throws \Throwable If secure nonce generation or encryption fails
     */
    public function encrypt(string $secret): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($secret, $nonce, $this->key);
        return base64_encode($nonce . $cipher);
    }

    /**
     * Authenticates a stored ciphertext before returning the TOTP secret
     *
     * @param string $encoded Base64 nonce and ciphertext from storage
     * @return string Authenticated plaintext Base32 TOTP secret
     * @throws \UnexpectedValueException If encoding or authentication fails
     */
    public function decrypt(string $encoded): string
    {
        $data = base64_decode($encoded, true);
        $nonceSize = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
        $minimumSize = $nonceSize + SODIUM_CRYPTO_SECRETBOX_MACBYTES;
        if ($data === false || strlen($data) < $minimumSize) {
            throw new \UnexpectedValueException('Invalid MFA ciphertext');
        }
        $nonce = substr($data, 0, $nonceSize);
        $cipher = substr($data, $nonceSize);
        $secret = sodium_crypto_secretbox_open($cipher, $nonce, $this->key);
        if ($secret === false) {
            throw new \UnexpectedValueException(
                'MFA ciphertext authentication failed'
            );
        }
        return $secret;
    }
}
