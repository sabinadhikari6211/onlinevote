<?php

// AES encryption class
class AES {
    private $key;

    public function __construct($key) {
        $this->key = $key;
    }

    // Encrypt a string using AES
    public function encrypt($data) {
        $iv = random_bytes(16); // Generate a random IV
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted); // Concatenate IV and encrypted data
    }

    // Decrypt a string using AES
    public function decrypt($data) {
        $data = base64_decode($data);  // Decode the base64 data
        $iv = substr($data, 0, 16);    // Extract the first 16 bytes as the IV
        $encrypted = substr($data, 16); // Extract the encrypted data
        return openssl_decrypt($encrypted, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, $iv); // Decrypt the data
    }
}

// AES key (32-byte key for AES-256)
$aesKey = 'your-32-byte-long-encryption-key'; // Replace with a secure 32-byte key
$aes = new AES($aesKey);

?>
