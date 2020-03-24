<?php
/* Generic Create Operation
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION:
 */
namespace Geekcow\FonyCore\Controller\UserOperations;

use Geekcow\FonyCore\Helpers\AllowCore;
use Geekcow\FonyCore\CoreModel\ApiUser;
use Geekcow\FonyCore\CoreModel\ApiClient;
use Geekcow\FonyCore\CoreModel\ApiUserType;
use Geekcow\FonyCore\CoreModel\ApiUserAsoc;
use Geekcow\FonyCore\CoreModel\ApiClientScope;
use Geekcow\FonyCore\Utils\ConfigurationUtils;

class UserCreate
{
  public $response;

  private $user;
  private $user_type;
  private $api_client;
  private $api_client_scopes;
  private $api_user_asoc;
  private $allowed_roles;

  //THE USER TOKEN IS THE CLIENT ASSOCIATED TO GENERATE LOGIN OPERATIONS
  private $user_token = '';

  public function __construct($allowed_roles){
    $config = ConfigurationUtils::getInstance();
    $this->user_token = $config->getUserClient();

    $this->user = new ApiUser();
    $this->user_type = new ApiUserType();
    $this->api_client = new ApiClient();
    $this->api_client_scopes = new ApiClientScope();
    $this->api_user_asoc = new ApiUserAsoc();

    $this->response = array();
    $this->allowed_roles = $allowed_roles;
  }

  public function createUser($scope, $asoc = 1){
    $username = md5(strtolower($_POST['email']));
    $fullname = $_POST['name'].' '.((isset($_POST['lastname']))?$_POST['lastname']:"");
    if ($this->user_exists($username)){
			$this->response['type'] = 'error';
      $this->response['title'] = 'Create user';
      $this->response['message'] = 'User already exist';
			$this->response['code'] = 409;
    }else if ($this->validate_password() && $this->validate_scope($scope)){

      $this->response = array();
			$password = sha1($_POST['password']);
      $this->user->columns['username'] = strtolower($username);
      $this->user->columns['name'] = $_POST['name'];
			$this->user->columns['lastname'] = (isset($_POST['lastname']))?$_POST['lastname']:"";
			$this->user->columns['email'] = $_POST['email'];
      $this->user->columns['phone'] = (isset($_POST['phone']))?$_POST['phone']:"";
      $this->user->columns['type'] = $_POST['type'];
			$this->user->columns['avatar'] = "";
      $this->user->columns['avatar_path'] = "";
      $this->user->columns['password'] = (isset($password))?$password:"";
      $this->user->columns['enabled'] = 1;
      $this->user->columns['verified'] = 1;
      $this->user->columns['verification'] = "";
			$this->user->columns['created_at'] = strtotime("now");
			$this->user->columns['updated_at'] = strtotime("now");

	    $id = $this->user->insert();
      if (is_numeric($id)){
        $client = '';
        if ($this->validate_api($this->user, $client, $asoc)){
    	    if ($this->user->fetch_id(array('username' => $username))){
            $this->response['entidad'] = $this->user->columns;
    				$this->response['code'] = 200;
          }else{
            $this->response['type'] = 'error';
            $this->response['title'] = 'Display user';
            $this->response['message'] = 'The following error has happened: '.$this->user->err_data;
        		$this->response['code'] = 500;
          }
        }
      }else{
        $this->response['type'] = 'error';
        $this->response['title'] = 'Create user';
        $this->response['message'] = 'The following error has happened: '.$this->user->err_data;
  			$this->response['code'] = 422;
      }
    }
  }

  //Private Methods
  private function validate_password(){
    $valid = false;
    if (isset($_POST['password']) && $_POST['password'] != ""){
      $valid = true;
    }
    if (!$valid){
      $this->response['type'] = 'error';
      $this->response['title'] = 'Create User';
      $this->response['message'] = 'Not assigned authentication';
      $this->response['code'] = 422;
    }
    return $valid;
  }

  private function validate_scope($scope){
    if (!AllowCore::is_allowed($scope, $this->allowed_roles)){
      $this->response = AllowCore::denied($scope);
      return false;
    }
    return true;
  }

	private function user_exists($user){
  	$validation = $this->user->fetch_id(array('username'=>$user));

    if (!$validation){
    	$this->response['type'] = 'error';
      $this->response['title'] = 'User';
      $this->response['message'] = 'User does not exist';
			$this->response['code'] = 422;
    }

    return $validation;
  }

  private function validate_api($user, &$client, $asoc = 1){
    $scope_to_use = "";
    if ($this->user_type->fetch_id(array('id' => $user->columns['type']))){
      $scope_to_use = $this->user_type->columns['scope'];
    }else{
      $this->remove_user($user->columns['username']);
      $this->response['type'] = 'error';
      $this->response['title'] = 'User type error';
      $this->response['message'] = 'The following error has happened: '.$this->user_type->err_data;
      $this->response['code'] = 500;
      return false;
    }
		$client = sha1($user->columns['username'].$user->columns['email'].date("Y-m-d H:i:s"));
		$secret = sha1($client.'SECRETKEY');
  	$this->api_client->columns['client_id'] = $client;
    $this->api_client->columns['client_secret'] = $secret;
		$this->api_client->columns['email'] = $user->columns['email'];
		$this->api_client->columns['user_id'] = $user->columns['username'];
    $this->api_client->columns['enabled'] = 1;
		$this->api_client->columns['asoc'] = $asoc;
		$this->api_client->columns['created_at'] = strtotime("now");
		$this->api_client->columns['updated_at'] = strtotime("now");

    $id = $this->api_client->insert();
    if (is_numeric($id)){
      if ($this->api_client->fetch_id(array('client_id' => $client))){
      	$this->api_client_scopes->columns['id_client'] = $client;
				$this->api_client_scopes->columns['id_scope'] = $scope_to_use;
				$idx = $this->api_client_scopes->insert();
        if (is_numeric($idx)){
          if ($this->api_client_scopes->fetch_id(array('id_client' => $client,'id_scope'=>$scope_to_use))){
            if ($this->validate_association($user->columns['username'], $client) && $this->validate_association($user->columns['username'], $this->user_token)){
              $this->broadcast_message($user->columns['type'], $user->columns['verification']);
              return true;
            }
          }else{
            $this->remove_user($user->columns['username']);
            $this->response['type'] = 'error';
            $this->response['title'] = 'Scope association validation';
            $this->response['message'] = 'The following error has happened: '.$this->api_client_scopes->err_data;
    				$this->response['code'] = 500;
						return false;
          }
        }else{
          $this->remove_user($user->columns['username']);
          $this->response['type'] = 'error';
          $this->response['title'] = 'Scope association creation';
          $this->response['message'] = 'The following error has happened: '.$this->api_client_scopes->err_data;
					$this->response['code'] = 422;
					return false;
        }
      }else{
        $this->remove_user($user->columns['username']);
        $this->response['type'] = 'error';
        $this->response['title'] = 'Token error';
        $this->response['message'] = 'The following error has happened: '.$this->api_client->err_data;
    		$this->response['code'] = 500;
				return false;
      }
    }else{
      $this->remove_user($user->columns['username']);
      $this->response['type'] = 'error';
      $this->response['title'] = 'Create Token';
      $this->response['message'] = 'The following error has happened: '.$this->api_client->err_data;
			$this->response['code'] = 422;
			return false;
    }
    return false;
  }

  public function validate_association($userid, $token){
		$this->api_user_asoc->columns['client_id'] = $token;
		$this->api_user_asoc->columns['username'] = $userid;
    $idx = $this->api_user_asoc->insert();
    if ($this->api_user_asoc->fetch_id(array('client_id' => $token, 'username' => $userid))){
      return true;
    }else{
      $this->remove_user($userid);
      $this->response['type'] = 'error';
      $this->response['title'] = 'Association error';
      $this->response['message'] = 'The following error has happened: '.$this->api_user_asoc->err_data;
			$this->response['code'] = 500;
			return false;
    }

  }

  private function broadcast_message($type=2,$verification){
    //custom email sending
    return true;
  }

  private function remove_user($id){
    if ($this->user->fetch_id( array("username" => $id) )){
	    if (!$this->user->delete()){
        $this->response['error_on_error'] = 'User could not be eliminated';
      }
    }
  }


}

?>
