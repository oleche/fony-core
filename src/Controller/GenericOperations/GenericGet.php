<?php

/* Generic Get Operation
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION:
 */

namespace Geekcow\FonyCore\Controller\GenericOperations;

use Geekcow\FonyCore\Controller\CoreOperation;

class GenericGet extends CoreOperation
{
    private $id;
    private $asc;
    private $ordering_field;
    private $custom_query;

    public function __construct($model, $session, $id = null)
    {
        parent::__construct($model, $session);
        $this->id = $id;
        $this->asc = true;
        $this->ordering_field = null;
        $this->custom_query = null;
    }

    public function setCustomQuery($val = null)
    {
        $this->custom_query = $val;
    }

    public function setOrderingField($orderingField, $asc = false)
    {
        $this->ordering_field = $orderingField;
        $this->asc = $asc;
    }

    public function get()
    {
        $map = $this->model->get_mapping();

        $query = $this->model->assembly_search($_GET);

        $this->model->set_pagination(true);
        $this->model->set_paging($this->model, $_GET);

        $pk = "";

        $q_list = array();

        foreach ($map as $k => $map) {
            if (isset($map['pk']) && $map['pk'] == true) {
                $pk = $k;
                break;
            }
        }

        if ($this->checkUser) {
            $query = "$this->usernameKey = '" . $this->session->username . "' " . ((trim($query) != "") ? " AND " . $query : $query);
        }

        if (is_null($this->id) || $this->id == "") {
            $orderBy = $this->ordering_field;
            if (is_null($this->ordering_field)) {
                $orderBy = array($pk);
            }

            if (is_null($this->custom_query) || (is_string($this->custom_query) && trim($this->custom_query) == "")) {
                $q_list = $this->model->fetch($query, false, $orderBy, $this->asc, $this->model->page);
            } else {
                $q_list = $this->model->fetch($this->custom_query, false, $orderBy, $this->asc, $this->model->page);
            }
        } else {
            if ($this->model->fetch_id(array($pk => $this->id), null, true, $query)) {
                $q_list[] = $this->model;
            }
        }

        if ((count($q_list) == 0) || (!$q_list)) {
            $this->response['type'] = 'error';
            $this->response['message'] = 'Cannot retrieve data';
            $this->response['code'] = 404;
        } else {
            $this->model->paginate($this->model);

            $this->response['code'] = 200;
            $this->response[$this->getClassName($this->model)] = array();
            foreach ($q_list as $k => $q_item) {
                $this->response[$this->getClassName($this->model)][] = $q_item->columns;
            }
        }
    }

    public function getPaginationLink()
    {
        return $this->model->pagination_link;
    }
}
