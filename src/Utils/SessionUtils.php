<?php
/* Session Utils
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Session support using internal token handling
 */
namespace Geekcow\FonyCore\Utils;

use Geekcow\FonyCore\Utils\Oauth\Oauth;

class SessionUtils {
  // Hold the class instance.
  private static $instance = null;

  public $username;
  public $session_scopes;
  public $err;
  public $response;
  private $authenticator;

  public function __construct($authenticator){
    $this->response = array();
		$this->username = '';
    $this->authenticator = $authenticator;
  }

  public function setAuthenticator($authenticator){
    $this->authenticator = $authenticator;
  }

  public function validateBearerToken($token){
    if ($this->authenticator->validateBearerToken($token)){
      $this->username = $this->authenticator->getUsername();
      $this->session_scopes = $this->authenticator->getScopes();
      return true;
    }else{
      $this->response['type'] = 'error';
      $this->response['code'] = 401;
      $this->response['message'] = $this->authenticator->getErr();
      return false;
    }
	}

  // The object is created from within the class itself
  // only if the class has no instance.
  public static function getInstance($authenticator = null)
  {
    if (self::$instance == null)
    {
      if (is_null($authenticator)){
        $authenticator = new Oauth();
      }
      self::$instance = new SessionUtils($authenticator);
    }

    return self::$instance;
  }
}

?>
