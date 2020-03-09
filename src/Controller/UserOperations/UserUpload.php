<?php
namespace Geekcow\FonyCore\Controller\UserOperations;
use Geekcow\FonyCore\CoreModel\ApiUser;
use Geekcow\FonyCore\CoreModel\ApiAssetType;
use Geekcow\FonyCore\Helpers\AllowCore;
use Geekcow\FonyCore\Utils\ConfigurationUtils;

class UserUpload{
  private $user;
  private $assetType;
  private $session;
  private $id;
  private $validScope;
  private $username;
  private $config;

  public $response;

  public function __construct($session, $id = null){
    $this->user = new ApiUser();
    $this->assetType = new ApiAssetType();
    $this->validScope = AllowCore::ADMINISTRATOR();
    $this->config = ConfigurationUtils::getInstance();
    $this->response = array();
    $this->session = $session;
    $this->id = $id;
  }

  public function setValidScope($scope){
    $this->validScope = $scope;
  }

  public function put($file){
    if ($this->validateScope($this->session->session_scopes)){
      $q_list = array();

      $theMap = $this->user->get_mapping();

      $pk = "";
      foreach ($theMap as $k => $map) {
        if (isset($map['pk']) && $map['pk'] == true){
          $pk = $k;
          break;
        }
      }
      if ($this->user->fetch_id(array($pk=>$this->id),null,true,""))
        $q_list[] = $this->user;

      if (count($q_list) > 0){
        if ($this->doUpdate($q_list[0], $theMap, $file)){
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
  }

  private function doUpdate($model, $theMap, $file){
    //Base file type detection
    $file_info = new finfo(FILEINFO_MIME);  // object oriented approach!
    $mime_type = $file_info->buffer($file);  // e.g. gives "image/jpeg"
    $mime_type = explode(';', $mime_type);

    $this->username = $model->columns['username'];
    if (!$this->validateUser()){
      return false;
    }

    foreach ($theMap as $k => $map) {
      $currentValue = $model->columns[$k];
      if (isset($map['foreign'])){
        $currentValue = $model->columns[$k][$map['foreign'][0]];
      }
      $model->columns[$k] = $currentValue;
    }
    if (isset($model->columns['updated_at'])){
      $model->columns['updated_at'] = strtotime("now");
    }
    if ($this->prepare_asset($mime_type[0], $this->username, $filepath, $filename, $tip, $model->columns['avatar_path'])){
      $model->columns['avatar_path'] = str_replace(MY_ASSET_ROOT,"",$filepath);
      $model->columns['avatar'] = $filename;
      if (!$model->update()){
        $this->response['type'] = 'error';
        $this->response['title'] = 'Update model';
        $this->response['message'] = 'Could not update';
        $this->response['code'] = 422;
        return false;
      }
      file_put_contents($filepath, $file);
      return true;
    }
  }

  private function validateUser(){
    if ($this->session->username != $this->username){
      $this->response['type'] = 'error';
      $this->response['title'] = 'User';
      $this->response['message'] = 'Cannot display this information';
			$this->response['code'] = 401;
      return false;
    }
  }

  private function validateScope($scope){
    if (!AllowCore::is_allowed($scope, $this->validScope)){
      $this->response = AllowCore::denied($scope);
      return false;
    }
    return true;
  }

  private function prepare_asset($mime, $id, &$filepath, &$filename, &$tip, $oldfile){
		$validation = false;

		if (!is_null($mime) && trim($mime) != ''){
			$q_list = $this->assetType->fetch(" mime LIKE '%$mime%' ");

			if (count($q_list) > 0){
				$validation = true;
				if (!file_exists(MY_ASSET_ROOT.'/'.$id)) {
			    mkdir(MY_ASSET_ROOT.'/'.$id, 0777, true);
				}
        if (trim($oldfile) != "" && file_exists(MY_ASSET_ROOT.'/'.$oldfile)) {
          $this->remove_asset($oldfile);
        }
				$filepath = MY_ASSET_ROOT.'/'.$id.'/avatar'.$q_list[0]->columns['format'];
				$filename = $this->config->getFileUrl().'/'.$id."/avatar".$q_list[0]->columns['format'];
				$tip = $q_list[0];
			}else{
        $this->response['type'] = 'alert';
        $this->response['title'] = 'Invalid file';
        $this->response['message'] = 'Invalid file, use valid files (jpg, png, bmp, gif, mp3, ogg, wav)';
				$this->response['code'] = 415;
			}
		}else{
			$this->response['type'] = 'error';
      $this->response['title'] = 'Invalid format';
      $this->response['message'] = 'Invalid format, use valid format (jpg, png, bmp, gif, mp3, ogg, wav)';
			$this->response['code'] = 415;
		}

		return $validation;
	}

  private function remove_asset($url){
    $filepath = MY_ASSET_ROOT.'/'.$url;
    if (unlink($filepath)){
      return true;
    }else{
      $this->response['type'] = 'error';
      $this->response['title'] = 'Delete file';
      $this->response['message'] = 'Could not delete file';
  		$this->response['code'] = 500;
      return false;
    }
  }
}

?>
