<?php

/* Token Utils
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Static methods for handling decoding and encoding operations
 */

namespace Geekcow\FonyCore\Utils;

/**
 * Class TokenUtils
 * @package Geekcow\FonyCore\Utils
 */
class TokenUtils
{
    /**
     * @param $token
     * @param $type
     * @return string|string[]
     */
    public static function sanitizeToken($token, $type)
    {
        return str_replace($type, '', $token);
    }

    /**
     * @param $token
     * @param $type
     * @return bool
     */
    public static function validateTokenSanity($token, $type)
    {
        return (strpos($token, $type) !== false);
    }

    /**
     * @param $input
     * @return string
     */
    public static function base64UrlEncode($input)
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    /**
     * @param $input
     * @return false|string
     */
    public static function base64UrlDecode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Returns an encrypted & utf8-encoded
     */
    public static function encrypt($pure_string, $encryption_key)
    {
        $method = 'AES-256-CBC';

        // hash
        $key = hash('sha256', $encryption_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $encryption_key), 0, 16);

        $encrypted_string = openssl_encrypt(utf8_encode($pure_string), $method, $key, 0, $iv);
        return $encrypted_string;
    }

    /**
     * Returns decrypted original string
     */
    public static function decrypt($encrypted_string, $encryption_key)
    {
        $method = 'AES-256-CBC';

        // hash
        $key = hash('sha256', $encryption_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $encryption_key), 0, 16);

        $decrypted_string = openssl_decrypt($encrypted_string, $method, $key, 0, $iv);
        return $decrypted_string;
    }
}
