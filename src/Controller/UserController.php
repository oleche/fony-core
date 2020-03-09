<?php
/* API user controller
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: User controller
 */
namespace Geekcow\FonyCore\Controller;

use Geekcow\FonyCore\Controller\BaseController;
use Geekcow\FonyCore\Controller\ApiMethods;
use Geekcow\FonyCore\Helpers\AllowCore;
use Geekcow\FonyCore\Controller\GenericOperations\GenericCreate;
use Geekcow\FonyCore\Controller\GenericOperations\GenericGet;
use Geekcow\FonyCore\Controller\GenericOperations\GenericPut;
use Geekcow\FonyCore\Controller\GenericOperations\GenericDelete;
use Geekcow\FonyCore\Controller\UserOperations\UserCreate;
use Geekcow\FonyCore\Controller\UserOperations\UserGetActions;
use Geekcow\FonyCore\Controller\UserActions\UserGetActions;
use Geekcow\FonyCore\Controller\UserActions\UserPutActions;

use Geekcow\FonyCore\CoreModel\ApiUser;
use Geekcow\FonyCore\Utils\ConfigurationUtils;

class UserController extends BaseController implements ApiMethods
{
  public function __construct($configfile = ConfigurationUtils::getInstance(MY_DOC_ROOT . "/src/config/config.ini")) {
		parent::__construct($configfile);
	}

  //CREATE
  public function doPOST($args = array(), $verb = null) {
    if (!$this->validation_fail)
    {
      if (is_array($args) && empty($args)){
        if ($this->validate_fields($_POST, 'api/user', 'POST')){
          $user_create = new UserCreate($this->allowed_roles);
          $user_create->createUser($this->session->session_scopes);
          $this->response = $user_create->response;
        }
      }else{
        $this->response['type'] = 'error';
        $this->response['title'] = 'User';
        $this->response['code'] = 404;
        $this->response['message'] = "Invalid URL";
      }
    }
    $this->filter_response(['notes']);
  }

  //READ INFORMATION
  public function doGET($args = array(), $verb = null) {
    if (!$this->validation_fail)
    {
      if (is_array($args) && empty($args)){
        $this->setExecutableClass(new UserGetActions());
        $this->setActionVerb($verb);
        $this->setActionId($verb);
        $this->execute();
      }else{
        $this->response['type'] = 'error';
        $this->response['title'] = 'User';
        $this->response['code'] = 404;
        $this->response['message'] = "Invalid URL";
      }
    }
    //$this->filter_response(['notes']);
    if ($this->session->username != $verb && !AllowCore::is_allowed($this->session->session_scopes, $this->allowed_roles)){
        $this->filter_response(['notes','password','email','avatar_path','phone','enabled','verification','updated_at']);
    }else{
        $this->filter_response(['notes','password']);
    }
  }

  //TEND TO HAVE MULTIPLE METHODS
  public function doPUT($args = array(), $verb = null, $file = null) {
    if (!$this->validation_fail)
    {
      $user_put_actions = new UserPutActions();
      $user_put_actions->setFile($file);
      $this->setExecutableClass($user_put_actions);
      if (is_array($args) && empty($args)){
        $this->setActionId($verb);
        $this->execute();
      }else{
        $this->setActionId($verb);
        $this->setActionVerb($args[0]);
        $this->execute(true);
      }
    }

    if ($this->session->username != $verb && !AllowCore::is_allowed($this->session->session_scopes, $this->allowed_roles)){
        $this->filter_response(['notes','password','email','avatar_path','phone','enabled','verification','created_at','updated_at']);
    }else{
        $this->filter_response(['notes','password']);
    }
  }

  //DELETES ONE SINGLE ENTRY
  public function doDELETE($args = array(), $verb = null) {
    if (!$this->validation_fail)
    {
      $this->response['code'] = 200;
      $this->response['msg'] = "OK";
    }
  }

  private function validateArgsAndVerb($args, $verb){

  }
}

?>
