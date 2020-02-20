<?php
/* Generic Create Operation
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION:
 */
namespace Geekcow\FonyCore\Controller\UserOperations;
use Geekcow\FonyCore\CoreModel\ApiUser;
use Geekcow\FonyCore\CoreModel\ApiUserAsoc;
use Geekcow\FonyCore\Helpers\AllowCore;

class UserPut{
  private $user;
  private $session;
  private $userid;
  private $validScope;

  public $response;

  public function __construct($session, $userid = null){
    $this->user = new ApiUser();
    //YOU CAN ADD MORE MODELS THAT NEED TO BE INITIALIZED HERE

    $this->validScope = AllowCore::ADMINISTRATOR();
    $this->response = array();
    $this->session = $session;
    $this->userid = $userid;
  }

  public function putUser(){
    if ($this->validateScope() && $this->validateUser()){
      $verified = " enabled = 1 ";

  		if ($this->user->fetch_id(array('username'=>$this->userid),null,true,"$verified")){
        if ($this->updateUser()){
          $this->response['code'] = 200;
    			$this->response['message'] = 'OK';
          $this->response['title'] = 'User updated';
        }
      }else{
        $this->response['type'] = 'error';
        $this->response['message'] = 'Cannot retrieve data';
        $this->response['code'] = 422;
      }
    }
  }

  public function setValidScope($scope){
    $this->validScope = $scope;
  }

  public function changePassword(){
    if ($this->validateScope()){
      //old_password
      $password = sha1($_POST['old_password']);
      if ($this->user->fetch_id(array('username' => $this->userid),null,true," password = '$pass' AND enabled = 1 ")){
        if ($this->updatePassword()){
          $this->response['code'] = 200;
          $this->response['message'] = 'OK';
          $this->response['title'] = 'Password updated';
        }
      }else{
        $this->response['type'] = 'error';
        $this->response['message'] = 'Cannot retrieve data';
        $this->response['code'] = 422;
      }
    }
  }

  public function enable(){
    if ($this->validateScope()){
      //old_password
      $verified = "";

      if ($this->user->fetch_id(array('username'=>$this->userid),null,true,"$verified")){
        if ($this->updateEnabled(1)){
          $this->response['code'] = 200;
          $this->response['message'] = 'OK';
          $this->response['title'] = 'User activated';
        }
      }else{
        $this->response['type'] = 'error';
        $this->response['message'] = 'Cannot retrieve data';
        $this->response['code'] = 422;
      }
    }
  }

  public function disable(){
    if ($this->validateScope()){
      //old_password
      $verified = "";

      if ($this->user->fetch_id(array('username'=>$this->userid),null,true,"$verified")){
        if ($this->updateEnabled(0)){
          $this->response['code'] = 200;
          $this->response['message'] = 'OK';
          $this->response['title'] = 'User deactivated';
        }
      }else{
        $this->response['type'] = 'error';
        $this->response['message'] = 'Cannot retrieve data';
        $this->response['code'] = 422;
      }
    }
  }

  private function updateUser(){
    $this->user->columns['name'] = (isset($_POST['name']))?$_POST['name']:$this->user->columns['name'];
    $this->user->columns['lastname'] = (isset($_POST['lastname']))?$_POST['lastname']:$this->user->columns['lastname'];
    $this->user->columns['phone'] = (isset($_POST['phone']))?$_POST['phone']:$this->user->columns['phone'];
    $this->user->columns['type'] = $this->user->columns['type']['id'];
    $this->user->columns['updated_at'] = strtotime("now");
    if (!$this->user->update()){
      $this->response['type'] = 'error';
      $this->response['title'] = 'User';
      $this->response['message'] = 'Cannot update';
      $this->response['code'] = 422;
      return false;
    }
    return true;
  }

  private function updatePassword(){
    $password = sha1($_POST['password']);
    $this->user->columns['password'] = $password;
    $this->user->columns['type'] = $this->user->columns['type']['id'];
    $this->user->columns['updated_at'] = strtotime("now");
    if (!$this->user->update()){
      $this->response['type'] = 'error';
      $this->response['title'] = 'User';
      $this->response['message'] = 'Cannot update';
      $this->response['code'] = 422;
      return false;
    }
    return true;
  }

  private function updateEnabled($value){
    $this->user->columns['enabled'] = $value;
    $this->user->columns['type'] = $this->user->columns['type']['id'];
    $this->user->columns['updated_at'] = strtotime("now");
    if (!$this->user->update()){
      $this->response['type'] = 'error';
      $this->response['title'] = 'User';
      $this->response['message'] = 'Cannot update';
      $this->response['code'] = 422;
      return false;
    }
    return true;
  }

  private function validateUser(){
    if ($this->session->username != $this->userid){
      $this->response['type'] = 'error';
      $this->response['title'] = 'User';
      $this->response['message'] = 'Invalid user';
			$this->response['code'] = 401;
      return false;
    }
    return true;
  }

  private function validateScope(){
    if (!AllowCore::is_allowed($this->session->session_scopes, $this->validScope)){
      $this->response = AllowCore::denied($this->session->session_scopes);
      return false;
    }
    return true;
  }
}

?>
