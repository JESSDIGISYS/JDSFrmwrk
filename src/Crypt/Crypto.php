<?php

namespace JDS\Crypt;

use JDS\Authentication\RuntimeException;
use Random\RandomException;

class Crypto
{

    public function __construct(private readonly string $key)
    {

    }

    /**
     * @throws RandomException
     * @throws RuntimeException
     */
    public function encrypt(string $data): string
    {
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc')); // Generate Initialization Vector
        $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $this->key, 0, $iv);

        if ($encryptedData === false) {
            throw new RuntimeException('Encryption failed');
        }

        // Return base64-encoded IV and encrypted data
        return base64_encode($iv . $encryptedData);
    }

    /**
     * @throws RuntimeException
     */
    public function decrypt(string $encrypted): string
    {
        $decodedData = base64_decode($encrypted);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($decodedData, 0, $ivLength); // Extract IV
        $ciphertext = substr($decodedData, $ivLength); // Extract encrypted content

        $decryptedData = openssl_decrypt($ciphertext, 'aes-256-cbc', $this->key, 0, $iv);

        if ($decryptedData === false) {
            throw new RuntimeException('Decryption failed');
        }

        return $decryptedData;
    }
}


