<?php
/* API authentication controller
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Class for authentication handling
 */
namespace Geekcow\FonyCore\Controller;

use Geekcow\FonyCore\Controller\CoreController;
use Geekcow\FonyCore\Controller\ApiMethods;
use Geekcow\FonyCore\Utils\TokenUtils;
use Geekcow\FonyCore\Utils\AuthUtils;

class AuthController extends CoreController implements ApiMethods
{
  const BASIC = 'Basic ';
  const BEARER = 'Bearer ';

  private $auth_handler;

  public $client_id;
	public $username;
  public $email;

  public function __construct($configfile = MY_DOC_ROOT . "/src/config/config.ini") {
		parent::__construct($configfile);
    $this->username = '';
		$this->response = array();

    $this->auth_handler = new AuthUtils($configfile);
	}

  public function doPOST($token = null, $params = array()){
		try{
      if (is_array($params) && empty($params)){
        if ($verb == 'refresh'){
          //TODO Refresh token
          $this->response['code'] = 501;
          $this->response['msg'] = "Not Implemented yet";
        }else{
          $this->response['code'] = 501;
          $this->response['msg'] = "Not Implemented";
        }
      }else{
        $token = TokenUtils::sanitize_token($token, self::BASIC);
        if (TokenUtils::validate_token_sanity($token, self::BASIC)){
          $this->auth_handler->setScopes((isset($params['scope']) && $params['scope'] != '')?$params['scope']:'');
      		if ($this->auth_handler->validate_basic($params) && $this->auth_handler->validate_scopes()){
            if ($this->auth_handler->getAsoc() == 1){
              if ($this->validate_fields($params, 'login', 'POST')){
                if (!$this->auth_handler->validate_login($params)){
                  $this->buildErrorSet();
          				return false;
                }
              }else{
                return false;
          		}
            }

    				$this->response['code'] = 200;
    				$this->response['access_token'] = $this->auth_handler->generate_token();
            $this->response['token_type'] = 'bearer';
            $this->response['username'] = $this->auth_handler->getUsername();
    				$this->response['expires'] = ((time($this->auth_handler->getApiToken()->columns['updated_at'])*1000)+$this->auth_handler->getApiToken()->columns['expires']) - (time()*1000);
    				return true;
    			}else{
            $this->buildErrorSet();
    				return false;
    			}
  			}else{
  				$this->response['type'] = 'error';
  		    $this->response['code'] = 401;
          $this->response['message'] = 'Malformed token';
  				return false;
  			}
      }

		}catch(Exception $e){
			$this->response['type'] = 'error';
			$this->response['code'] = 500;
			$this->response['message'] = $this->err;
			return false;
		}
  }

  public function doGET() {
    $this->response['code'] = 501;
    $this->response['msg'] = "Not Implemented";
  }
  public function doPUT() {
    $this->response['code'] = 501;
    $this->response['msg'] = "Not Implemented";
  }
  public function doDELETE() {
    $this->response['code'] = 501;
    $this->response['msg'] = "Not Implemented";
  }

  private function buildErrorSet(){
    $this->response['type'] = 'error';
    if (isset($this->response['http_code']) && trim($this->response['http_code']) != "")
      $this->response['code'] = 422;
    else if ($this->err == 'User disabled')
      $this->response['code'] = 423;
    else
      $this->response['code'] = 401;
    $this->response['message'] = $this->auth_handler-getErr();
  }
}

?>
