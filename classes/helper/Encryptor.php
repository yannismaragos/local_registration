<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Encryptor class.
 *
 * Encryptor class for securely encrypting and decrypting sensitive data.
 * The class uses AES-256-CBC encryption for reversible encryption and decryption.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_registration;

/**
 * Encryptor class.
 *
 * @package     local_registration
 * @copyright   2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Encryptor {
    /**
     * The key used for encryption and decryption.
     *
     * @var string
     */
    private $key;

    /**
     * The cipher algorithm used for encryption and decryption.
     *
     * @var string
     */
    private static $cipher = 'aes-256-cbc';

    /**
     * The number of bytes used for generating cryptographic values.
     *
     * @var int
     */
    private static $bytes = 32;

    /**
     * Encryptor constructor.
     *
     * Constructor to initialize the Encryptor object with the encryption key.
     *
     * @param string $key The key used for encryption and decryption.
     */
    public function __construct($key) {
        $this->key = $key;
    }

    /**
     * Encrypts text using AES-256-CBC encryption.
     *
     * @param string $text The text to be encrypted.
     *
     * @return string The encrypted text in base64-encoded format.
     */
    public function encrypt($text) {
        // Generate a random initialization vector (IV).
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(static::$cipher));

        // Encrypt the text using AES-256-CBC encryption.
        $value = $iv . openssl_encrypt($text, static::$cipher, $this->key, OPENSSL_RAW_DATA, $iv);

        // Calculate the HMAC of the value (IV + encrypted) and prepend it.
        $hmac = hash_hmac('sha256', $value, $this->key, true);
        $value = $hmac . $value;

        // Encode in base64.
        return base64_encode($value);
    }

    /**
     * Decrypts encrypted text using AES-256-CBC decryption.
     *
     * @param string $encryptedtext The encrypted text in base64-encoded format.
     *
     * @return string|false The decrypted text or false on decryption failure.
     */
    public function decrypt($encryptedtext) {
        // Decode the base64-encoded value.
        $data = base64_decode($encryptedtext);

        // Extract the HMAC, IV, and encrypted text.
        $hmac = substr($data, 0, static::$bytes);
        $iv = substr($data, static::$bytes, openssl_cipher_iv_length(static::$cipher));
        $encryptedtext = substr($data, static::$bytes + openssl_cipher_iv_length(static::$cipher));

        // Verify the integrity using HMAC.
        $calculatedhmac = hash_hmac('sha256', $iv . $encryptedtext, $this->key, true);
        if (!hash_equals($hmac, $calculatedhmac)) {
            // HMAC verification failed; data integrity compromised.
            return false;
        }

        // Decrypt the text using AES-256-CBC decryption.
        $encryptedtext = openssl_decrypt($encryptedtext, static::$cipher, $this->key, OPENSSL_RAW_DATA, $iv);

        return $encryptedtext;
    }
}
