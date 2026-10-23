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
    private $custom_params;
    private $force_custom;

    public function __construct($model, $session, $id = null)
    {
        parent::__construct($model, $session);
        $this->id = $id;
        $this->asc = true;
        $this->ordering_field = null;
        $this->custom_query = null;
        $this->custom_params = [];
        $this->force_custom = false;
    }

    /**
     * @param string|\Geekcow\Dbcore\QueryBuilder|null $val WHERE conditions, or the whole query when $force is set
     * @param bool $force runs $val as a custom query
     * @param array $params values for the ? placeholders of a custom query
     */
    public function setCustomQuery($val = null, $force = false, array $params = [])
    {
        $this->custom_query = $val;
        $this->force_custom = $force;
        $this->custom_params = $params;
    }

    public function setOrderingField($orderingField, $asc = false)
    {
        $this->ordering_field = $orderingField;
        $this->asc = $asc;
    }

    public function get()
    {
        $map = $this->model->get_mapping();

        $params = [];
        $query = $this->model->assembly_search($_GET, $params);

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
            $query = "$this->usernameKey = ? " . ((trim($query) != "") ? " AND " . $query : $query);
            array_unshift($params, $this->session->username);
        }

        // PHP 7 compared 0 == "" as true, so 0 (and false) also mean "no id" here
        if (is_null($this->id) || in_array($this->id, ["", 0, false], true) || $this->id === 0.0) {
            $orderBy = $this->ordering_field;
            if (is_null($this->ordering_field)) {
                $orderBy = array($pk);
            }

            if (is_null($this->custom_query) || (is_string($this->custom_query) && trim($this->custom_query) == "")) {
                $q_list = $this->model->fetch($query, false, $orderBy, $this->asc, $this->model->page, $params);
            } else {
                // custom queries are only ordered when an ordering field was set; the search parameters do not
                // apply to them
                $q_list = $this->model->fetch(
                    $this->custom_query,
                    $this->force_custom,
                    $this->force_custom ? $this->ordering_field : $orderBy,
                    $this->asc,
                    $this->model->page,
                    $this->custom_params
                );
            }
        } else {
            if ($this->model->fetch_id(array($pk => $this->id), null, true, $query, 0, $params)) {
                $q_list[] = $this->model;
            }
        }

        if ((!$q_list) || (count($q_list) == 0)) {
            $this->response['type'] = 'error';
            $this->response['message'] = 'Cannot retrieve data';
            $this->response['code'] = 404;
        } else {
            $this->model->paginate($this->model);

            $this->response['code'] = 200;
            $this->response[$this->getClassName($this->model)] = array();
            foreach ($q_list as $k => $q_item) {
                $columns = (is_object($q_item) && !is_null($q_item->columns)) ? $q_item->columns : $q_item;
                $this->response[$this->getClassName($this->model)][] = $columns;
            }
        }
    }

    public function getPaginationLink()
    {
        return $this->model->pagination_link;
    }
}
