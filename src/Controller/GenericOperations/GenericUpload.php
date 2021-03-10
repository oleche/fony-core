<?php

namespace Geekcow\FonyCore\Controller\GenericOperations;

use Geekcow\FonyCore\CoreModel\ApiAssetType;
use Geekcow\FonyCore\Helpers\AllowCore;

class GenericUpload
{
    private $model;
    private $assetType;
    private $session;
    private $id;
    public $response;
    private $validScope;
    private $checkUser;
    private $path;
    private $assetUrl;

    private $file_url;

    public function __construct($model, $session, $id = null, $file_url = "test.com")
    {
        $this->model = $model;
        $this->assetType = new ApiAssetType();
        $this->file_url = $file_url;
        $this->response = array();
        $this->session = $session;
        $this->id = $id;
        $this->validScope = AllowCore::ADMINISTRATOR();
        $this->checkUser = false;
        $this->path = null;
        $this->assetUrl = null;
    }

    public function checkUser()
    {
        $this->checkUser = true;
    }

    public function setValidScope($scope)
    {
        $this->validScope = $scope;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function setAssetUrl($assetUrl)
    {
        $this->assetUrl = $assetUrl;
    }

    public function put($params)
    {
        if ($this->validateScope($this->session->session_scopes)) {
            $q_list = array();

            $theMap = $this->model->get_mapping();

            $pk = "";
            foreach ($theMap as $k => $map) {
                if (isset($map['pk']) && $map['pk'] == true) {
                    $pk = $k;
                    break;
                }
            }

            if ($this->model->fetch_id(array($pk => $this->id), null, true, "")) {
                if ($this->doUpdate($theMap, $params)) {
                    $this->response['code'] = 200;
                    $this->response['message'] = 'OK';
                    $this->response['title'] = 'Model updated';
                }
            } else {
                $this->response['type'] = 'error';
                $this->response['message'] = 'Cannot retrieve data';
                $this->response['code'] = 422;
            }
        }
    }

    private function doUpdate($theMap, $params)
    {
        //Base file type detection
        $file_info = new finfo(FILEINFO_MIME);  // object oriented approach!
        $mime_type = $file_info->buffer($params);  // e.g. gives "image/jpeg"
        $mime_type = explode(';', $mime_type);

        $folderName = $this->id;

        if ($this->checkUser) {
            $folderName = $this->model->columns['username']['username'];
            if (!$this->validateUser($folderName)) {
                return false;
            }
        }

        foreach ($theMap as $k => $map) {
            $currentValue = $this->model->columns[$k];
            if (isset($map['foreign'])) {
                $currentValue = $this->model->columns[$k][$map['foreign'][0]];
            }
            $this->model->columns[$k] = $currentValue;
        }
        if (isset($this->model->columns['updated_at'])) {
            $this->model->columns['updated_at'] = date("Y-m-d H:i:s");
        }
        if (
            $this->prepareAsset(
                $mime_type[0],
                $folderName,
                $filepath,
                $filename,
                $tip,
                (!is_null($this->path)) ? $this->model->columns[$this->path] : null
            )
        ) {
            if (!is_null($this->path)) {
                $this->model->columns[$this->path] = str_replace(MY_ASSET_ROOT, "", $filepath);
            }
            if (!is_null($this->assetUrl)) {
                $this->model->columns[$this->assetUrl] = $filename;
            }
            if (!$this->model->update()) {
                $this->response['type'] = 'error';
                $this->response['title'] = 'Update model';
                $this->response['message'] = 'Cannot update';
                $this->response['code'] = 422;
                return false;
            }
            file_put_contents($filepath, $params);
            return true;
        }
    }

    private function validateUser($username)
    {
        if (!AllowCore::is_allowed($this->session->session_scopes, AllowCore::ADMINISTRATOR())) {
            if ($this->session->username != $username) {
                $this->response['type'] = 'error';
                $this->response['title'] = 'User';
                $this->response['message'] = 'Cannot show this information';
                $this->response['code'] = 401;
                return false;
            }
        }
        return true;
    }

    private function validateScope($scope)
    {
        if (!AllowCore::is_allowed($scope, $this->validScope)) {
            $this->response = AllowCore::denied($scope);
            return false;
        }
        return true;
    }

    private function prepareAsset($mime, $id, &$filepath, &$filename, &$tip, $oldfile = null)
    {
        $validation = false;

        if (!is_null($mime) && trim($mime) != '') {
            $q_list = $this->assetType->fetch(" mime LIKE '%$mime%' ");

            if (count($q_list) > 0) {
                $validation = true;
                if (!file_exists(MY_ASSET_ROOT . '/' . $id)) {
                    mkdir(MY_ASSET_ROOT . '/' . $id, 0777, true);
                }
                if (trim($oldfile) != "" && file_exists(MY_ASSET_ROOT . '/' . $oldfile)) {
                    $this->removeAsset($oldfile);
                }
                $filepath = MY_ASSET_ROOT . '/' . $id . '/' . time() . $q_list[0]->columns['format'];
                $filename = $this->file_url . '/' . $id . "/" . time() . $q_list[0]->columns['format'];
                $tip = $q_list[0];
            } else {
                $alertMessage = 'The file is invalid, please use a valid file (jpg, png, bmp, gif, mp3, ogg, wav)';
                $this->response['type'] = 'alert';
                $this->response['title'] = 'Invalid file';
                $this->response['message'] = $alertMessage;
                $this->response['code'] = 415;
            }
        } else {
            $alertMessage = 'The file format is invalid, please use a valid format (jpg, png, bmp, gif, mp3, ogg, wav)';
            $this->response['type'] = 'error';
            $this->response['title'] = 'Invalid format';
            $this->response['message'] = $alertMessage;
            $this->response['code'] = 415;
        }

        return $validation;
    }

    private function removeAsset($url)
    {
        $filepath = MY_ASSET_ROOT . '/' . $url;
        if (unlink($filepath)) {
            return true;
        } else {
            $this->response['type'] = 'error';
            $this->response['title'] = 'Delete file';
            $this->response['message'] = 'The file could not be removed';
            $this->response['code'] = 500;
            return false;
        }
    }
}
