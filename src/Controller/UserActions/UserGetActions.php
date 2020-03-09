<?php

namespace Geekcow\FonyCore\Controller\UserActions;
use Geekcow\FonyCore\Controller\CoreActions;
use Geekcow\FonyCore\Controller\CoreActionsInterface;
use Geekcow\FonyCore\Controller\GenericOperations\GenericCount;
use Geekcow\FonyCore\CoreModel\ApiUser;
use Geekcow\FonyCore\Helpers\AllowCore;

class UserGetActions extends CoreActions implements CoreActionsInterface{

  public function __construct(){
    parent::__construct();
  }

  public function default($id){
    $user_get = new UserGet($this->session, $id);
    $user_get->getUser();
    $this->response = $user_get->response;
    $this->pagination_link = $user_get->getPaginationLink();
  }

  public function count($id=null){
    if (!AllowCore::is_allowed($this->session->session_scopes, $this->allowed_roles)){
      $this->response = AllowCore::denied($this->session->session_scopes);
      return false;
    }

    $user_count = new GenericCount(new ApiUser(), $this->session);
    $user_count->setValidScope($this->allowed_roles);
    $user_count->getCount();
    $this->response = $user_count->response;
  }

  public function countgroup($id=null){
    if (!AllowCore::is_allowed($this->session->session_scopes, $this->allowed_roles)){
      $this->response = AllowCore::denied($this->session->session_scopes);
      return false;
    }

    $user_count = new GenericCount(new ApiUser(), $this->session);
    $user_count->setGroupingArray(array('created_at' => 'DAY(created_at)'));
    $user_count->setValidScope($this->allowed_roles);
    $user_count->setTotal(false);
    $user_count->getCount(true);
    $this->response = $user_count->response;
  }
}

?>
