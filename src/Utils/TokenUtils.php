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
        return str_replace($type, '', (string)$token);
    }

    /**
     * @param $token
     * @param $type
     * @return bool
     */
    public static function validateTokenSanity($token, $type)
    {
        return (strpos((string)$token, $type) !== false);
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

        $encrypted_string = openssl_encrypt(self::latin1ToUtf8($pure_string), $method, $key, 0, $iv);
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

    /**
     * Same conversion as utf8_encode(), which is deprecated since PHP 8.2
     *
     * @param $string
     * @return string
     */
    public static function latin1ToUtf8($string)
    {
        $string = (string)$string;
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
        }
        $output = '';
        $length = strlen($string);
        for ($i = 0; $i < $length; $i++) {
            $char = ord($string[$i]);
            $output .= ($char < 0x80) ? $string[$i] : chr(0xC0 | ($char >> 6)) . chr(0x80 | ($char & 0x3F));
        }
        return $output;
    }

    /**
     * Same conversion as utf8_decode(), which is deprecated since PHP 8.2
     *
     * @param $string
     * @return string
     */
    public static function utf8ToLatin1($string)
    {
        $string = (string)$string;
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
        }
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $string);
            if ($converted !== false) {
                return $converted;
            }
        }
        return $string;
    }
}
