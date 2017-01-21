<?php
class EncryptionException extends Exception {}

/**
 * Encapsulates data encryption over openssl using AES-256 cypher.
 */
final class Encryption {
    const CYPHER_METHOD = "AES-256-CBC";
    
    private $secret;
    
    /**
     * Creates an encryption instance using a secret password that's going to be used in encryption/decryption.
     * @param string $secret Encryption password.
     */
    public function __construct($secret) {
        $this->secret = $secret;
    }
    
    /**
     * Encrypts data and returns encrypted value.
     * 
     * @param string $data Value to encrypt.
     * @throws EncryptionException If encryption fails.
     * @return string Encrypted representation of data.
     */
    public function encrypt($data){
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encryptedData = openssl_encrypt($data, self::CYPHER_METHOD, $this->secret, 0, $iv);
        if($encryptedData === false) throw new EncryptionException("Encryption failed!");
        return base64_encode($iv.$encryptedData);
    }
    
    /**
     * Decrypts data and returns decrypted value.
     * 
     * @param string $data Encrypted representation of data.
     * @throws EncryptionException If decryption fails.
     * @return string Decrypted data.
     */
    public function decrypt($data){
        $data = base64_decode($data);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = substr($data, 0, $iv_size);
        $result = openssl_decrypt(substr($data, $iv_size), self::CYPHER_METHOD, $this->secret, 0, $iv);
        if($result===false) throw new EncryptionException("Decryption failed!");
        return $result;
    }
}
