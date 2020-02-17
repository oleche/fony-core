<?php
/* Generic Delete Operation
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION:
 */
namespace Geekcow\FonyCore\Controller\GenericOperations;

use Geekcow\FonyCore\Helpers\AllowCore;

class GenericDelete
{
  private $model;
  private $id;
  private $session;
  private $checkUser;
  private $validationExclusion;

  public $response;

  public function __construct($object, $id, $session){
    $this->model = $object;
    $this->response = array();
    $this->id = $id;
    $this->session = $session;
    $this->checkUser = false;
    $this->validationExclusion = null;
  }

  public function checkUser(){
    $this->checkUser = true;
  }

  public function setValidationExclusion($validationExclusion){
    $this->validationExclusion = $validationExclusion;
  }

  public function getModel(){
    return $this->model;
  }

  public function delete(){
    $map = $this->model->get_mapping();
    $pk = "";

    foreach ($map as $k => $map) {
      if (isset($map['pk']) && $map['pk'] == true){
        $pk = $k;
        break;
      }
    }

    if ($this->model->fetch_id(array($pk=>$this->id))){
      if ($this->checkUser){
        $username = $this->model->columns['username']['username'];
        if (!$this->validateUser($username)){
          return false;
        }
      }
      if (!$this->model->delete()){
        $this->response['type'] = 'error';
        $this->response['title'] = 'Delete model';
        $this->response['message'] = 'The following message has been produced: '.$this->model->err_data;
        $this->response['code'] = 422;
      }else{
        $this->response['message'] = 'Deleted';
        $this->response['code'] = 200;

      }
    }else{
      $this->response['type'] = 'error';
      $this->response['message'] = 'Cannot retrieve data';
      $this->response['code'] = 404;
    }
  }

  //Private Methods
  private function validateUser($username){
    if (!is_null($this->validationExclusion) && AllowCore::is_allowed($this->session->session_scopes, $this->validationExclusion)){
      return true;
    } else {
      if ($this->session->username != $this->username){
        $this->response['type'] = 'error';
        $this->response['title'] = 'User';
        $this->response['message'] = 'Cannot show this information';
  			$this->response['code'] = 401;
        return false;
      } else {
        return true;
      }
    }
  }
}

?>
