<?php

/* Allow Core
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Base role/scope validation
 */

namespace Geekcow\FonyCore\Helpers;

class AllowCore
{
    private static $SYSTEM = array('system');

    /**
     * @return string[]
     */
    public static function SYSTEM()
    {
        return self::$SYSTEM;
    }

    /**
     * @param $scopes
     * @param $allow
     * @return bool
     */
    public static function isAllowed($scopes, $allow)
    {
        $set = explode(',', $scopes);
        $r_values = false;
        foreach ($set as $value) {
            $value = trim($value);
            $r_values = ($r_values || in_array($value, $allow));
        }
        return $r_values;
    }

    /**
     * @param $scopes
     * @return array
     */
    public static function denied($scopes)
    {
        $response = array();
        $response['code'] = 401;
        $response['type'] = 'error';
        $response['message'] = 'Cannot allow action under scopes: ' . $scopes;
        return $response;
    }
}
