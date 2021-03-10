<?php

/* Generic Count Operation
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION:
 */

namespace Geekcow\FonyCore\Controller\GenericOperations;

use Geekcow\FonyCore\Controller\CoreOperation;

class GenericCount extends CoreOperation
{
    private $total;
    private $id;
    private $grouping_array;

    public function __construct($model, $session, $id = array())
    {
        parent::__construct($model, $session);
        $this->id = $id;
        $this->total = true;
        $this->grouping_array = array();
    }

    public function setTotal($set)
    {
        $this->total = $set;
    }

    public function setGroupingArray($array)
    {
        $this->grouping_array = $array;
    }

    public function getCount($latest = false)
    {
        $query = $this->model->assembly_search($_GET);

        if (!empty($this->id)) {
            foreach ($this->id as $key => $value) {
                $query .= (trim($query) != "") ? " AND " : "";
                $query .= "$key = '$value'";
            }
        }

        if ($latest) {
            $query .= (trim($query) != "") ? " AND " : "";
            $query .= "created_at >= DATE_ADD(CURDATE(), INTERVAL -2 MONTH)";
        }
        if ($this->checkUser) {
            if (trim($query) != "") {
                $query .= " AND ";
            }
            $query .= " username = '$this->session->username'";
        }

        $q_list = array();
        if ($this->total) {
            $q_list = $this->model->count($query);
        } else {
            //array('created_at' => 'DAY(created_at)')
            $q_list = $this->model->count($query, $this->grouping_array);
        }

        if (count($q_list) > 0) {
            $this->response['count'] = array();
            $this->response['code'] = 200;
            foreach ($q_list as $k => $q_item) {
                $this->response['count'][] = $q_item;
            }
        } else {
            $this->response['type'] = 'error';
            $this->response['message'] = 'Cannot retrieve data';
            $this->response['code'] = 422;
        }
    }
}
