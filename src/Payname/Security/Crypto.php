<?php
namespace Payname\Security;

use Payname\Exception\ApiCryptoException;

/**
 * Class Crypto
 * Extract from https://github.com/illuminate/encryption
 */
class Crypto
{
    /**
     * The algorithm used for encryption.
     *
     * @var string
     */
    private static $cipher = 'AES-128-CBC';

    /**
     * Encrypt the given value.
     *
     * @param  string  $value
     * @param  string  $key
     * @return string
     */
    public static function encrypt($value, $key)
    {
        $iv = openssl_random_pseudo_bytes(self::getIvSize());
        $value = openssl_encrypt(serialize($value), self::$cipher, $key, 0, $iv);

        if ($value === false) {
            throw new ApiCryptoException('Could not encrypt the data.');
        }

        $mac = self::hash($iv = base64_encode($iv), $value, $key);

        return base64_encode(json_encode(compact('iv', 'value', 'mac')));
    }

    /**
     * Decrypt the given value.
     *
     * @param  string  $payload
     * @param  string  $key
     * @return string
     */
    public static function decrypt($value, $key)
    {
        $payload = self::getJsonPayload($value, $key);
        $iv = base64_decode($payload['iv']);
        $decrypted = openssl_decrypt($payload['value'], self::$cipher, $key, 0, $iv);

        if ($decrypted === false) {
            throw new ApiCryptoException('Could not decrypt the data.');
        }

        return unserialize($decrypted);
    }

    /**
     * Get the JSON array from the given payload.
     *
     * @param  string  $payload
     * @param  string  $key
     * @return array
     */
    private static function getJsonPayload($payload, $key)
    {
        $payload = json_decode(base64_decode($payload), true);

        if (!$payload || (!is_array($payload) || !isset($payload['iv']) || !isset($payload['value']) || !isset($payload['mac']))) {
            throw new ApiCryptoException('The payload is invalid.');
        }
        if (!self::validMac($payload, $key)) {
            throw new ApiCryptoException('The MAC is invalid.');
        }
        return $payload;
    }

    /**
     * Determine if the MAC for the given payload is valid.
     *
     * @param  array  $payload
     * @param  string  $key
     * @return bool
     */
    private static function validMac(array $payload, $key)
    {
        $bytes = null;

        if (function_exists('random_bytes')) {
            $bytes = random_bytes(16);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(16, $strong);
            if ($bytes === false || $strong === false) {
                throw new ApiCryptoException('Unable to generate random string.');
            }
        } else {
            throw new ApiCryptoException('OpenSSL extension is required for PHP 5 users.');
        }

        $calcMac = hash_hmac('sha256', self::hash($payload['iv'], $payload['value'], $key), $bytes, true);

        return hash_hmac('sha256', $payload['mac'], $bytes, true) === $calcMac;
    }

    /**
     * Create a MAC for the given value.
     *
     * @param  string  $iv
     * @param  string  $value
     * @return string
     */
    private static function hash($iv, $value, $key)
    {
        return hash_hmac('sha256', $iv.$value, $key);
    }

    /**
     * Get the IV size for the cipher.
     *
     * @return int
     */
    private static function getIvSize()
    {
        return 16;
    }
}
