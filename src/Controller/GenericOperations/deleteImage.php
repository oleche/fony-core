<?php
class GenericDeleteImage
{
  private $model;
  private $id;
  private $session;
  private $checkUser;
  private $validScope;
  private $path;

  public $response;

  public function __construct($model, $id, $session){
    $this->model = $model;
    $this->response = array();
    $this->id = $id;
    $this->session = $session;
    $this->checkUser = false;
    $this->path = null;
    $this->validScope = allow::PROFILE();
  }

  public function checkUser(){
    $this->checkUser = true;
  }

  public function setPath($path){
    $this->path = $path;
  }

  public function setValidScope($scope){
    $this->validScope = $scope;
  }

  public function getModel(){
    return $this->model;
  }

  public function delete(){
    if ($this->validateScope()){

      if ($this->model->fetch_id(array('id'=>$this->id))){
        if (is_null($this->path)){
          $this->response['type'] = 'error';
          $this->response['message'] = 'Path not set';
          $this->response['code'] = 422;
          return false;
        }
        $filepath = $this->model->columns[$this->path];
        if ($this->checkUser){
          $username = $this->model->columns['username']['username'];
          if (!$this->validateUser($username)){
            return false;
          }
        }
        if (!$this->model->delete()){
          $this->response['type'] = 'error';
          $this->response['title'] = 'Borrar modelo';
          $this->response['message'] = 'The following message has been produced: '.$this->model->err_data;
          $this->response['code'] = 422;
        }else{
          if ($this->remove_asset($filepath)){
            $this->response['message'] = 'Deleted';
            $this->response['code'] = 200;
          }
        }
      }else{
        $this->response['type'] = 'error';
        $this->response['message'] = 'Cannot retrieve data';
        $this->response['code'] = 422;
      }
    }
  }

  //Private Methods
  private function validateScope(){
    if (!allow::is_allowed($this->session->session_scopes, $this->validScope)){
      $this->response = allow::denied($scope);
      return false;
    }
    return true;
  }

  private function remove_asset($url){
    if (isset($url) && (trim($url) != "")){
      $filepath = MY_ASSET_ROOT.'/'.$url;
      if (file_exists($filepath)) {
        if (unlink($filepath)){
          return true;
        }else{
          $this->response['type'] = 'error';
          $this->response['title'] = 'Delete file';
          $this->response['message'] = 'The file could not be deleted';
          $this->response['code'] = 500;
          return false;
        }
      }
    }
    return true;
  }

  private function validateUser($username){
    if (!allow::is_allowed($this->session->session_scopes, allow::ADMINISTRATOR())){
      if ($this->session->username != $username){
        $this->response['type'] = 'error';
        $this->response['title'] = 'User';
        $this->response['message'] = 'Cannot show this information';
  			$this->response['code'] = 401;
        return false;
      }
    }
    return true;
  }

}

?>
