<?php
/* Generic Put Operation
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION:
 */
namespace Geekcow\FonyCore\Controller\GenericOperations;

use Geekcow\FonyCore\Helpers\AllowCore;

class GenericPut
{
  private $model;
  private $session;
  private $id;
  private $username;
  private $usernameKey;
  private $customParameters;
  private $validationExclusion;
  public $response;

  public function __construct($model, $session, $id = null){
    $this->model = $model;
    $this->response = array();
    $this->customParameters = array();
    $this->session = $session;
    $this->id = $id;
    $this->username = null;
    $this->$validationExclusion = null;
  }

  public function setUsername($username, $key = 'username'){
    $this->username = $username;
    $this->usernameKey = $key;
  }

  public function setCustomParameters($params){
    $this->customParameters = $params;
  }

  public function setValidationExclusion($validationExclusion){
    $this->validationExclusion = $validationExclusion;
  }

  public function getModel(){
    return $this->model;
  }

  public function put(){
    $theMap = $this->model->get_mapping();
    $pk = "";
    foreach ($theMap as $k => $map) {
      if (isset($map['pk']) && $map['pk'] == true){
        $pk = $k;
        break;
      }
    }
    $q_list = $this->queryModel($pk);

    if (count($q_list) > 0){
      if ($this->doUpdate($q_list[0], $theMap, $pk)){
        //sanity check
        $this->queryModel($pk);
        $this->response['code'] = 200;
        $this->response['message'] = 'OK';
        $this->response['title'] = 'Model updated';
      }
    }else{
      $this->response['type'] = 'error';
      $this->response['message'] = 'Cannot retrieve data';
      $this->response['code'] = 422;
    }
  }

  private function queryModel($pk){
    $q_list = array();
    if (!is_null($this->username)){
      if ($this->model->fetch_id(array($pk=>$this->id),null,true,"$this->usernameKey LIKE '$this->username'")){
        $q_list[] = $this->model;
      }
    }else{
      if ($this->model->fetch_id(array($pk=>$this->id),null,true,""))
        $q_list[] = $this->model;
    }
    return $q_list;
  }

  private function doUpdate($model, $theMap, $pk){
    if (isset($model->columns[$pk]))
      $this->id = $model->columns[$pk];

    $this->username = $model->columns[$this->usernameKey]['username'];
    if (!is_null($this->username)){
      if (!$this->validateUser()){
        return false;
      }
    }

    foreach ($theMap as $k => $map) {
      $currentValue = $model->columns[$k];
      if (isset($map['foreign'])){
        $currentValue = $model->columns[$k][$map['foreign'][0]];
      }
      if (isset($this->customParameters[$k])){
        $model->columns[$k] = $this->customParameters[$k];
      }else if (isset($map['postable']) && $map['postable'] == true){
        $model->columns[$k] = (isset($_POST[$k]))?$_POST[$k]:$currentValue;
      }else{
        $model->columns[$k] = $currentValue;
      }
    }
    if (isset($model->columns['updated_at'])){
      $model->columns['updated_at'] = date("Y-m-d H:i:s");
    }
    if (!$model->update()){
      $this->response['type'] = 'error';
      $this->response['title'] = 'Update model';
      $this->response['message'] = 'Cannot update';
      $this->response['code'] = 422;
      return false;
    }
    return true;
  }

  private function validateUser(){
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
