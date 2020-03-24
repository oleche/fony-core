<?php
/* Token Utils
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Static methods for handling decoding and encoding operations
 */
namespace Geekcow\FonyCore\Utils;

class TokenUtils {
  public static function sanitizeToken($token, $type){
    return str_replace($type, '', $token);
  }

  public static function validateTokenSanity($token, $type){
    return (strpos($token, $type) !== false);
  }

  public static function base64UrlEncode($input) {
   return strtr(base64_encode($input), '+/', '-_');
  }

  public static function base64UrlDecode($input) {
   return base64_decode(strtr($input, '-_', '+/'));
  }

  /**
   * Returns an encrypted & utf8-encoded
   */
  public static function encrypt($pure_string, $encryption_key) {
      $iv_size = openssl_cipher_iv_length('AES-128-ECB');
      $iv = openssl_random_pseudo_bytes($iv_size);

      $encrypted_string = openssl_encrypt(utf8_encode($pure_string), 'AES-128-ECB', $encryption_key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
      return $encrypted_string;
  }

  /**
   * Returns decrypted original string
   */
  public static function decrypt($encrypted_string, $encryption_key) {
      $iv_size = openssl_cipher_iv_length('AES-128-ECB');
      $iv = openssl_random_pseudo_bytes($iv_size);
      $decrypted_string = openssl_encrypt ($encrypted_string , 'AES-128-ECB', $encryption_key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
      return $decrypted_string;
  }
}

?>
