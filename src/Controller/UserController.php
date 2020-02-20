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

use Geekcow\FonyCore\CoreModel\ApiUser;

class UserController extends BaseController implements ApiMethods
{
  public function __construct($configfile = MY_DOC_ROOT . "/src/config/config.ini") {
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
        if ($verb == 'count'){
          $model = new ApiUser();
          $user_count = new GenericCount($model, $this->session);
          $user_count->setValidScope($this->allowed_roles);
          $user_count->getCount();
          $this->response = $user_count->response;
        } elseif ($verb == 'countgroup'){
          $model = new ApiUser();
          $user_count = new GenericCount($model, $this->session);
          $user_count->setGroupingArray(array('created_at' => 'DAY(created_at)'));
          $user_count->setValidScope($this->allowed_roles);
          $user_count->setTotal(false);
          $user_count->getCount(true);
          $this->response = $user_count->response;
        } else {
          $user_get = new UserGet($this->session, $verb);
          $user_get->getUser();
          $this->response = $user_get->response;
          $this->pagination_link = $user_get->getPaginationLink();
        }
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
      if (is_array($args) && empty($args)){
        if ($this->validate_fields($_POST, 'api/user/:id', 'PUT')){
          $user_put = new UserPut($this->session, $verb);
          $user_put->setValidScope($this->allowed_roles);
          $user_put->putUser();
          $this->response = $user_put->response;
        }
      }else{
        switch ($args[0]) {
          case 'upload':
            if ($this->validate_upload($file)){
              $user_put = new UserUpload($this->session, $verb, $this->file_url);
              $user_put->setValidScope($this->allowed_roles);
              $user_put->put($file);
              $this->response = $user_put->response;
            }
            break;
          case 'password':
            if ($this->validate_fields($_POST, 'api/user/:id/password', 'PUT')){
              $status_put = new UserPut($this->session, $verb);
              $status_put->setValidScope($this->allowed_roles);
              $status_put->changePassword();
              $this->response = $status_put->response;
            }
            break;
          case 'enable':
            $status_put = new UserPut($this->session, $verb);
            $status_put->setValidScope($this->allowed_roles);
            $status_put->enable();
            $this->response = $status_put->response;
            break;
          case 'disable':
            $status_put = new UserPut($this->session, $verb);
            $status_put->setValidScope($this->allowed_roles);
            $status_put->disable();
            $this->response = $status_put->response;
            break;
          default:
            $this->response['type'] = 'error';
            $this->response['title'] = 'User';
            $this->response['code'] = 404;
            $this->response['message'] = "Invalid URL";
            break;
        }
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
