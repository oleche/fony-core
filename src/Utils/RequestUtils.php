<?php

/* Request Utils
 * DESCRIPTION: Parsing and cleaning of the request input
 */

namespace Geekcow\FonyCore\Utils;

class RequestUtils
{
    /**
     * Fields that are never cleaned, so passwords and secrets reach the code exactly as they were sent
     */
    public const RAW_FIELDS = '/pass|secret/i';

    /**
     * Media type of the request without its parameters, i.e. "application/json" for
     * "application/json; charset=utf-8"
     *
     * @param string|null $content_type
     * @return string
     */
    public static function mediaType($content_type = null)
    {
        if (is_null($content_type)) {
            $content_type = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');
        }
        return strtolower(trim(explode(';', (string)$content_type)[0]));
    }

    public static function isJson($media_type)
    {
        return $media_type === 'application/json' || substr($media_type, -5) === '+json';
    }

    /**
     * Fields of a raw body (PUT/DELETE requests, which PHP does not parse): JSON for JSON media types,
     * url-encoded fields otherwise. Multipart bodies are left alone (uploads read the raw body).
     *
     * @param string|null $body
     * @param string|null $media_type
     * @return array
     */
    public static function parseBody($body, $media_type = null)
    {
        $body = (string)$body;
        $media_type = is_null($media_type) ? self::mediaType() : $media_type;
        if ($body === '' || strpos($media_type, 'multipart/') === 0) {
            return [];
        }
        if (self::isJson($media_type)) {
            $decoded = json_decode($body, true);
            return is_array($decoded) ? $decoded : [];
        }
        $fields = [];
        parse_str($body, $fields);
        return $fields;
    }

    /**
     * Strips tags and trims every value, except the RAW_FIELDS ones
     *
     * @param mixed $data
     * @param string|int|null $key
     * @return mixed
     */
    public static function cleanInputs($data, $key = null)
    {
        if (is_array($data)) {
            $clean_input = array();
            foreach ($data as $k => $v) {
                $clean_input[$k] = self::cleanInputs($v, $k);
            }
            return $clean_input;
        }
        if (is_string($key) && preg_match(self::RAW_FIELDS, $key)) {
            return $data;
        }
        if (is_null($data) || is_bool($data)) {
            return $data;
        }
        return trim(strip_tags((string)$data));
    }
}
