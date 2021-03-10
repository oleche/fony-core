<?php

/* Generic Delete Operation
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION:
 */

namespace Geekcow\FonyCore\Controller\GenericOperations;

use Geekcow\FonyCore\Controller\CoreOperation;

class GenericDelete extends CoreOperation
{
    private $id;

    public function __construct($model, $session, $id = null)
    {
        parent::__construct($model, $session);
        $this->id = $id;
    }

    public function delete()
    {
        $map = $this->model->get_mapping();
        $pk = "";

        foreach ($map as $k => $map) {
            if (isset($map['pk']) && $map['pk'] == true) {
                $pk = $k;
                break;
            }
        }

        if ($this->model->fetch_id(array($pk => $this->id))) {
            if ($this->checkUser) {
                $username = $this->model->columns[$this->usernameKey]['username'];
                if (!$this->validateUser($username)) {
                    return false;
                }
            }
            if (!$this->model->delete()) {
                $this->response['type'] = 'error';
                $this->response['title'] = 'Delete model';
                $this->response['message'] = 'The following message has been produced: ' . $this->model->err_data;
                $this->response['code'] = 422;
            } else {
                $this->response['message'] = 'Deleted';
                $this->response['code'] = 200;
            }
        } else {
            $this->response['type'] = 'error';
            $this->response['message'] = 'Cannot retrieve data';
            $this->response['code'] = 404;
        }
    }
}
