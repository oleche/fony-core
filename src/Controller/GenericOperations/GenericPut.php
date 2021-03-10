<?php

/* Generic Put Operation
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION:
 */

namespace Geekcow\FonyCore\Controller\GenericOperations;

use Geekcow\FonyCore\Controller\CoreOperation;

class GenericPut extends CoreOperation
{
    private $id;
    private $custom_parameters;

    public function __construct($model, $session, $id = null)
    {
        parent::__construct($model, $session);
        $this->custom_parameters = array();
        $this->id = $id;
    }

    public function setCustomParameters($params)
    {
        $this->custom_parameters = $params;
    }

    public function put()
    {
        $theMap = $this->model->get_mapping();
        $pk = "";
        foreach ($theMap as $k => $map) {
            if (isset($map['pk']) && $map['pk'] == true) {
                $pk = $k;
                break;
            }
        }
        $q_list = $this->queryModel($pk);

        if (count($q_list) > 0) {
            if ($this->doUpdate($q_list[0], $theMap, $pk)) {
                //sanity check
                $this->queryModel($pk);
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

    private function queryModel($pk)
    {
        $q_list = array();
        if ($this->checkUser) {
            if (
                $this->model->fetch_id(
                    array($pk => $this->id),
                    null,
                    true,
                    "$this->usernameKey LIKE '$this->session->username'"
                )
            ) {
                $q_list[] = $this->model;
            }
        } else {
            if ($this->model->fetch_id(array($pk => $this->id), null, true, "")) {
                $q_list[] = $this->model;
            }
        }
        return $q_list;
    }

    private function doUpdate($model, $theMap, $pk)
    {
        if (isset($model->columns[$pk])) {
            $this->id = $model->columns[$pk];
        }

        if ($this->checkUser) {
            $username = $model->columns[$this->usernameKey]['username'];
            if (!$this->validateUser($username)) {
                return false;
            }
        }

        foreach ($theMap as $k => $map) {
            $currentValue = $model->columns[$k];
            if (isset($map['foreign'])) {
                $currentValue = $model->columns[$k][$map['foreign'][0]];
            }
            if (isset($this->custom_parameters[$k])) {
                $model->columns[$k] = $this->custom_parameters[$k];
            } else {
                if (isset($map['postable']) && $map['postable'] == true) {
                    $model->columns[$k] = (isset($_POST[$k])) ? $_POST[$k] : $currentValue;
                } else {
                    $model->columns[$k] = $currentValue;
                }
            }
        }
        if (isset($model->columns['updated_at'])) {
            $model->columns['updated_at'] = date("Y-m-d H:i:s");
        }
        if (!$model->update()) {
            $this->response['type'] = 'error';
            $this->response['title'] = 'Update model';
            $this->response['message'] = 'Cannot update';
            $this->response['code'] = 422;
            return false;
        }
        return true;
    }
}
