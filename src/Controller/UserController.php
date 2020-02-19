<?php
include_once MY_DOC_ROOT . '/controller/baseController.php';
include_once MY_DOC_ROOT . '/model/user/usertype.php';
include_once MY_DOC_ROOT . '/model/user/user.php';
include_once MY_DOC_ROOT . '/model/user/invitation.php';
include_once MY_DOC_ROOT . '/model/user/message.php';
include_once MY_DOC_ROOT . '/controller/generic/create.php';
include_once MY_DOC_ROOT . '/controller/generic/get.php';
include_once MY_DOC_ROOT . '/controller/user/create.php';
include_once MY_DOC_ROOT . '/controller/user/get.php';
include_once MY_DOC_ROOT . '/controller/user/put.php';
include_once MY_DOC_ROOT . '/controller/user/upload.php';
include_once MY_DOC_ROOT . '/controller/user/count.php';

include_once MY_DOC_ROOT . '/controller/helpers/allow.php';

class UserController extends BaseController implements ApiMethods
{
  public function __construct($configfile = MY_DOC_ROOT . "/core/config.ini") {
		parent::__construct($configfile);
	}

  //USUALLY TO CREATE
  public function doPOST($args = array(), $verb = null) {
    if (!$this->validation_fail)
    {
      if (is_array($args) && empty($args)){
        if ($this->validate_fields($_POST, 'api/user', 'POST')){
          $user_create = new UserCreate($this->site_url);
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

  //USUALLY TO READ INFORMATION
  public function doGET($args = array(), $verb = null) {
    if (!$this->validation_fail)
    {
      if (is_array($args) && empty($args)){
        if ($verb == 'count'){
          $model = new User();
          $user_count = new GenericCount($model, $this->session);
          $user_count->setValidScope(allow::ADMINISTRATOR());
          $user_count->getCount();
          $this->response = $user_count->response;
        } elseif ($verb == 'countgroup'){
          $model = new User();
          $user_count = new GenericCount($model, $this->session);
          $user_count->setGroupingArray(array('created_at' => 'DAY(created_at)'));
          $user_count->setValidScope(allow::ADMINISTRATOR());
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
        switch ($args[0]) {
          case 'message':
            $model = new Invitation();
            $get_message = new GenericGet($model, $this->session);
            $get_message->setCustomQuery(" ( source = '".$this->session->username."' AND destination = '".$verb."' ) OR ( source = '".$verb."' AND  destination = '".$this->session->username."' ) AND accepted = 1 AND blocked = 0 ");
            $get_message->get();
            $this->response = $get_message->response;
            if ($get_message->response['code'] == 200){
              $model = new Message();
              $get_asset = new GenericGet($model, $this->session);
              $get_asset->setCustomQuery(" ( source = '".$this->session->username."' AND destination = '".$verb."' ) OR ( source = '".$verb."' AND  destination = '".$this->session->username."' ) ");
              $get_asset->get();
              $this->response = $get_asset->response;
              $this->pagination_link = $get_asset->getPaginationLink();
            }
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
    //$this->filter_response(['notes']);
    if ($this->session->username != $verb && !allow::is_allowed($this->session->session_scopes, allow::ADMINISTRATOR())){
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
          $user_put->setValidScope(allow::PROFILE());
          $user_put->putUser();
          $this->response = $user_put->response;
        }
      }else{
        switch ($args[0]) {
          case 'upload':
            if ($this->validate_upload($file)){
              $user_put = new UserUpload($this->session, $verb, $this->file_url);
              $user_put->setValidScope(allow::PROFILE());
              $user_put->put($file);
              $this->response = $user_put->response;
            }
            break;
          case 'invite':
            $model = new Invitation();
            $parameters = array("source"=>$this->session->username,"destination"=>$verb);
            $invitation_create = new GenericCreate($model, array("source", "destination"));
            $invitation_create->setValidScope(allow::PROFILE());
            $invitation_create->setCustomParameters($parameters);
            $invitation_create->create($this->session->session_scopes);
            $this->response = $invitation_create->response;
            break;
          case 'message':
            if ($this->validate_fields($_POST, 'api/user/:id/message', 'PUT')){
              $model = new Invitation();
              $get_message = new GenericGet($model, $this->session);
              $get_message->setCustomQuery(" ( source = '".$this->session->username."' AND destination = '".$verb."' ) OR ( source = '".$verb."' AND  destination = '".$this->session->username."' ) AND accepted = 1 AND blocked = 0 ");
              $get_message->get();
              $this->response = $get_message->response;
              if ($get_message->response['code'] == 200){
                $model = new Message();
                $parameters = array("source"=>$this->session->username,"destination"=>$verb);
                $invitation_create = new GenericCreate($model);
                $invitation_create->setValidScope(allow::PROFILE());
                $invitation_create->setCustomParameters($parameters);
                $invitation_create->create($this->session->session_scopes);
                $this->response = $invitation_create->response;
              }
            }
            break;
          case 'enable':
            $status_put = new UserPut($this->session, $verb);
            $status_put->setValidScope(allow::ADMINISTRATOR());
            $status_put->enable();
            $this->response = $status_put->response;
            break;
          case 'disable':
            $status_put = new UserPut($this->session, $verb);
            $status_put->setValidScope(allow::ADMINISTRATOR());
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

    if ($this->session->username != $verb && !allow::is_allowed($this->session->session_scopes, allow::ADMINISTRATOR())){
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
