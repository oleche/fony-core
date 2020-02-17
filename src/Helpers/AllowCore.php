<?php
/* Allow Core
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Base role/scope validation
 */
namespace Geekcow\FonyCore\Helpers;

class AllowCore {
	private static $ADMINISTRATOR = array('administrator');
	private static $SYSTEM = array('system', 'administrator');

	public static function ADMINISTRATOR(){
  	return self::$ADMINISTRATOR;
	}

	public static function SYSTEM(){
		return self::$SYSTEM;
	}

	public static function is_allowed($scopes, $allow){
		$set = explode(',', $scopes);
		$r_values = true;
		foreach ($set as $value) {
			$value = trim($value);
			$r_values = ($r_values && in_array($value, $allow));
		}
		return $r_values;
	}

	public static function denied($scopes){
		$response = array();
		$response['code'] = 401;
		$response['type'] = 'error';
		$response['message'] = 'Cannot allow action under scopes: '.$scopes;
		return $response;
	}
}

?>
