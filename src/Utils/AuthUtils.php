<?php
/* Auth Utils
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Authentication support for token generation and general authentication
 */
namespace Geekcow\FonyCore\Utils;

use Geekcow\FonyCore\Utils\TokenUtils;
use Geekcow\FonyCore\Utils\ConfigurationUtils;
use Geekcow\FonyCore\CoreModel\ApiScope;
use Geekcow\FonyCore\CoreModel\ApiToken;
use Geekcow\FonyCore\CoreModel\ApiClient;
use Geekcow\FonyCore\CoreModel\ApiClientScope;
use Geekcow\FonyCore\CoreModel\ApiUserAsoc;

class AuthUtils {
  private $err;
  private $api_client;
  private $api_client_scope;
  private $api_token;
  protected $scope;
  private $session_handler;

  private $client_id;
  private $email;
  private $username;
  private $asoc;
  private $_scopes;
  private $config;

  public function __construct() {
    $this->config = ConfigurationUtils::getInstance();
    $this->api_client = new ApiClient();
    $this->api_client_scope = new ApiClientScope();
    $this->api_token = new ApiToken();
    $this->scope = new ApiScope();

    $this->session_handler = new SessionUtils();

  }

  public function getErr(){
    return $this->err;
  }

  public function getClientId(){
    return $this->client_id;
  }

  public function getEmail(){
    return $this->email;
  }

  public function getUsername(){
    return $this->username;
  }

  public function getAsoc(){
    return $this->asoc;
  }

  public function getApiToken(){
    return $this->api_token;
  }

  public function setScopes($scopes){
    $this->_scopes = $scopes;
  }

  /**
   * Validates a basic token to identify if its related to a valid and active
   * client
   *
   * @return BOOLEAN if found and assigns the client_id, email, username and asociation status
   *
   */
  public function validate_basic($params = array(), $token){
    $token64 = base64_decode($token);
    $tokens = explode(":", $token64);
    $result = $this->api_client->fetch("client_id = '$tokens[0]' AND client_secret = '$tokens[1]' AND enabled = 1");
    if (count($result) == 1){
      $this->client_id = $result[0]->columns['client_id'];
      $this->email = $result[0]->columns['email'];
      $this->username = $result[0]->columns['user_id']['username'];
      $this->asoc = $result[0]->columns['asoc'];
      return true;
    }else{
      $this->err = 'Token not found';
      return false;
    }
  }

  /**
   * Identifies if the scopes provided do exists in the database and are assigned
   * to the required username or client
   *
   * @return BOOLEAN if found
   *
   */
  public function validate_scopes($method){
    $retval = true;

    $scopes_arr = explode(',', $this->_scopes);
    if (count($scopes_arr) <= 0){
      $retval = ($retval && false);
      $this->err = "no scopes selected";
    }

    foreach ($scopes_arr as $value) {
      if ($this->scope->fetch_id(array("name"=>$value))){
        $result = $this->api_client_scope->fetch("id_client = '$this->client_id' AND id_scope = '".$this->scope->columns['name']."'");
        if (count($result) > 0){
          $retval = ($retval && true);
        } else {
          $retval = ($retval && false);
          $this->err = "invalid scope for client";
        }
      }else{
        $this->err = "scope '$value' not found";
        $retval = ($retval && false);
      }
    }
    return $retval;
  }

  /**
   * Generates a new token or fetch the latest active token
   * Structure of a token:
   *   client_id
   *   timestamp - created at
   *   scopes
   *   username
   *
   * @return TOKEN if found or created, or FALSE if error
   *
   */
  public function generate_token(){
		if ($this->locate_valid_token()){
			return $this->api_token->columns['token'];
		}else{
      $timestamp = time();
      $token = TokenUtils::encrypt($this->client_id.':'.$timestamp.':'.$this->_scopes.':'.$this->username,$this->config->getAppSecret());
      $token = TokenUtils::base64_url_encode($token);
      $this->api_token->columns['token'] = $token;
      $this->api_token->columns['username'] = $this->username;
      $this->api_token->columns['created_at'] = strtotime("now");
      $this->api_token->columns['updated_at'] = strtotime("now");
      $this->api_token->columns['expires'] = 3600000;
      $this->api_token->columns['enabled'] = 1;
      $this->api_token->columns['client_id'] = $this->client_id;
      $this->api_token->columns['scopes'] = $this->_scopes;
      $this->api_token->columns['timestamp'] = $timestamp;
      $insertResult = $this->api_token->insert();
      if (is_numeric($insertResult))
				return $token;
			else {
				$this->err = 'Error saving token: '.$this->api_token->err_data;
				throw new Exception("Error Processing Request", 1);
				return false;
			}
		}
	}

  /**
   * Finds a valid token based on the client and username and determines if can be
   * provided or it gets expired
   *
   * Structure of a token:
   *   client_id
   *   timestamp - created at
   *   scopes
   *   username
   *
   * @return BOOLEAN if found or created
   *
   */
  private function locate_valid_token(){
		$result = $this->api_token->fetch("client_id = '$this->client_id' AND username = '$this->username'", false, array('updated_at'), false);
		$last = false;
		foreach ($result as $r) {
			if (count($result) > 0){
				$token = $result[0]->columns['token'];
				$token = $this->decrypt(TokenUtils::base64_url_decode($token), $this->config->getAppSecret());
				$token = explode(':', $token);
				$token[2] = (string)$token[2];
				$token[3] = (string)$token[3];

				if (trim($this->username) == "" || trim($this->username) == trim($token[3])){
					if (count($token) == 4){
						if ((trim($this->_scopes) == trim($token[2])) && (((time($result[0]->columns['updated_at'])*1000)+$result[0]->columns['expires']) > (time()*1000))){
							$this->api_token = $result[0];
							$this->api_token->columns['updated_at'] = strtotime("now");
							$this->api_token->columns['client_id'] = $result[0]->columns['client_id']['client_id'];
              $this->api_token->columns['username'] = $result[0]->columns['username']['username'];
							$this->api_token->update();
							return true;
						}else{
							$result[0]->columns['enabled'] = 0;
							$result[0]->columns['client_id'] = $result[0]->columns['client_id']['client_id'];
              $result[0]->columns['username'] = $result[0]->columns['username']['username'];
							if (!$result[0]->update())
								$this->err = 'Error invalidating token';
							return false;
						}
					}else{
						$this->err = 'Malformed token';
						return false;
					}
				}else{
					$last = false;
				}
			}else{
				$last = false;
			}
		}
		return $last;

	}

  /**
   * Performs the login validation of the user and password
   *
   * @return BOOLEAN the user and password matches
   *
   */
	private function validate_login($params=array()){
      if ($this->session_handler->validate_login($params)){
        $this->username = $this->session_handler->username;
        return true;
      }else{
        $this->err = $this->session_handler->err;
        return false;
      }
	}
}

?>
