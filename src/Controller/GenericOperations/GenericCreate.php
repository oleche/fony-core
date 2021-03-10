<?php

/* Generic Create Operation
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION:
 */

namespace Geekcow\FonyCore\Controller\GenericOperations;

use Geekcow\FonyCore\Controller\CoreOperation;

class GenericCreate extends CoreOperation
{
    private $model;
    private $uniqueness_keys;
    private $custom_parameters;

    public function __construct($model, $session, $uniquenessKeys = null)
    {
        parent::__construct($model, $session);
        $this->custom_parameters = array();
        $this->uniqueness = null;
        if (!is_null($uniquenessKeys)) {
            $this->uniqueness_keys = $uniquenessKeys;
        }
    }

    public function setCustomParameters($params)
    {
        $this->custom_parameters = $params;
    }

    public function create()
    {
        //merge POST parameters with customParameters in order to validate uniqueness
        $mergedParameteres = array_merge($_POST, $this->custom_parameters);

        if (
            !is_null($this->uniqueness_keys)
            && !$this->validateUniqueness($this->uniqueness_keys, $mergedParameteres)
        ) {
            return false;
        }

        $this->response = array();
        $pk = $this->prepareData();

        $id = $this->model->insert();
        if (is_numeric($id)) {
            $this->model->fetch_id(array($pk => $id));
            $this->model->columns[$pk] = $id;
            $this->response[get_class($this->model)] = $this->model->columns;
            $this->response['code'] = 200;
        } else {
            $this->response['type'] = 'error';
            $this->response['title'] = 'Create model';
            $this->response['message'] = 'The following message has been produced: ' . $this->model->err_data;
            $this->response['code'] = 422;
        }
    }

    //Private Methods
    private function prepareData()
    {
        $theMap = $this->model->get_mapping();
        $pk = null;
        $hasUsernameColumn = false;

        foreach ($theMap as $k => $map) {
            if (isset($this->custom_parameters[$k])) {
                $this->model->columns[$k] = $this->custom_parameters[$k];
            }
            if (isset($_POST[$k]) && isset($map['postable']) && $map['postable'] == true) {
                $this->model->columns[$k] = $_POST[$k];
            }
            if (isset($map['default']) && is_null($this->model->columns[$k])) {
                $this->model->columns[$k] = $map['default'];
            }
            if ($k == $this->usernameKey) {
                $hasUsernameColumn = true;
            }
            if (!is_null($pk) && isset($map['pk']) && $map['pk'] == true) {
                $pk = $k;
            }
        }

        if ($hasUsernameColumn && !isset($_POST[$this->usernameKey])) {
            $this->model->columns[$this->usernameKey] = $this->session->username;
        }

        if (isset($theMap['updated_at'])) {
            $this->model->columns['updated_at'] = date("Y-m-d H:i:s");
        }

        if (isset($theMap['created_at'])) {
            $this->model->columns['created_at'] = date("Y-m-d H:i:s");
        }

        return $pk;
    }

    private function validateUniqueness($uniquenessKeys, $mergedParameteres)
    {
        if ($this->model->isUnique($uniquenessKeys, $mergedParameteres) == true) {
            return true;
        } else {
            $this->response[get_class($this->model)] = array();
            $this->response['type'] = 'error';
            $this->response['title'] = 'Create model';
            $this->response['message'] = 'The item is not unique';
            $this->response['code'] = 409;
            return false;
        }
    }
}
