<?php
/* Session Utils
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Session support using internal token handling
 */
namespace Geekcow\FonyCore\Utils;

use Geekcow\FonyCore\CoreModel\ApiUser;
use Geekcow\FonyCore\CoreModel\ApiToken;
use Geekcow\FonyCore\CoreModel\ApiUserAsoc;
use Geekcow\FonyCore\Utils\TokenUtils;

class SessionUtils {
  const BASIC = 'Basic ';
  const BEARER = 'Bearer ';

  protected $app_secret;
  protected $api_token;
  protected $api_user_asoc;
  protected $user;
  public $username;
  public $session_scopes;
  public $err;
  public $response;

  public function __construct($app_secret, $config_file = MY_DOC_ROOT . "/src/config/config.ini"){
    $this->app_secret = $app_secret;
    $this->api_token = new ApiToken($config_file);
    $this->api_user_asoc = new ApiUserAsoc($configfile);
    $this->user = new ApiUser($configfile);
    $this->response = array();
		$this->username = '';
  }

  /**
   * Performs the login validation of the user and password
   *
   * @return BOOLEAN the user and password matches
   *
   */
  public function validate_login($params=array()){
    $params['email'] = md5($params['email']);
    $result = array();
    $pass = sha1($params['password']);
    if ($this->user->fetch_id(array('username' => $params['email']),null,true," password = '$pass' AND enabled = 1 ")){
      if ($this->api_user_asoc->fetch_id(array('client_id'=>$this->client_id,'username'=>$this->user->columns['username']))){
        $this->username = trim($this->user->columns['username']);
        return true;
      }else{
        $this->err = 'User not associated';
        return false;
      }
    }else{
      if ($this->user->fetch_id(array('username' => $params['email']),null,true," enabled = 0 ")){
        $this->err = 'User disabled';
        return false;
      }else{
        $this->err = 'Invalid Credentials';
        return false;
      }
    }
  }

  /**
   * Validates if the token is active and valid then retrieves the scopes and username
   *
   * @return BOOLEAN the user and password matches
   *
   */
  private function validate_token($token){
    $result = $this->api_token->fetch("token = '$token' AND enabled = 1", false, array('updated_at'), false);
    if (count($result) == 1){
      $token = TokenUtils::decrypt(TokenUtils::base64_url_decode($token), $this->app_secret);
      $token = explode(':', $token);

      if (count($token) == 4){
        if (((time($result[0]->columns['updated_at'])*1000)+$result[0]->columns['expires']) > (time()*1000)){
          $this->session_scopes = $token[2];
          $this->username = trim($token[3]);

          return true;
        }else{
          $result[0]->columns['enabled'] = 0;
          $result[0]->columns['client_id'] = $result[0]->columns['client_id']['client_id'];
          $result[0]->columns['username'] = $result[0]->columns['username']['username'];
          $result[0]->update();
          $this->err = 'Expired token';
          return false;
        }
      }else{
        $this->err = 'Malformed token';
        return false;
      }
    }else{
      $this->err = 'Invalid token';
      return false;
    }
  }

  public function validate_bearer_token($token){
    try{
      $token = TokenUtils::sanitize_token($token, self::BEARER);
      if (TokenUtils::validate_token_sanity($token, self::BEARER)){
        if ($this->validate_token($token)){
          return true;
        }else{
          $this->response['type'] = 'error';
          $this->response['code'] = 401;
          $this->response['message'] = $this->err;
          return false;
        }
      }else{
        $this->response['type'] = 'error';
        $this->response['code'] = 401;
        $this->response['message'] = 'Malformed token';
        return false;
      }
    }catch(Exception $e){
      $this->response['type'] = 'error';
      $this->response['code'] = 401;
      $this->response['message'] = $this->err;
      return false;
    }
	}
}

?>
