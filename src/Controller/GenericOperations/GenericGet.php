<?php
/* Generic Get Operation
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION:
 */
namespace Geekcow\FonyCore\Controller\GenericOperations;

class GenericGet
{
  public $response;

  private $model;
  private $id;
  private $session;
  private $sessionValidation;
  private $asc;
  private $orderingField;
  private $customQuery;
  private $username;

  public function __construct($object, $session, $id = null){
    $this->model = $object;
    $this->response = array();
    $this->id = $id;
    $this->session = $session;
    $this->sessionValidation = false;
    $this->asc = true;
    $this->orderingField = null;
    $this->customQuery = null;
    $this->username = null;
  }

  public function setUsername($username = null){
    $this->username = $username;
  }

  public function setSessionValidation($val = false){
    $this->sessionValidation = $val;
  }

  public function setCustomQuery($val = null){
    $this->customQuery = $val;
  }

  public function setOrderingField($orderingField, $asc = false){
    $this->orderingField = $orderingField;
    $this->asc = $asc;
  }

  public function getModel(){
    return $this->model;
  }

  public function get(){
    $map = $this->model->get_mapping();

    $query = $this->model->assembly_search($_GET);

    $this->model->set_pagination(true);
    $this->model->set_paging($this->model, $_GET);

    if (!is_null($this->username)){
      $query = "username = '$this->username' ".( (trim($query) != "")?" AND ".$query:$query );

      $orderBy = $this->orderingField;

      if (is_null($this->orderingField)){
        $orderBy = array('username');
      }

      $q_list = $this->model->fetch("$query",false,$orderBy,$this->asc,$this->model->page);
    }else{
      $pk = "";

      $q_list = array();

      foreach ($map as $k => $map) {
        if (isset($map['pk']) && $map['pk'] == true){
          $pk = $k;
          break;
        }
      }

      if ($this->sessionValidation){
        $query = "username = '$this->session->username' ".( (trim($query) != "")?" AND ".$query:$query );
      }

      if (is_null($this->id) || $this->id == "") {
        $orderBy = $this->orderingField;
        if (is_null($this->orderingField)){
          $orderBy = array($pk);
        }

        if (is_null($this->customQuery) || trim($this->customQuery) == ""){
          $q_list = $this->model->fetch($query,false,$orderBy,$this->asc,$this->model->page);
        }else{
          $q_list = $this->model->fetch($this->customQuery,false,$orderBy,$this->asc,$this->model->page);
        }
      }else{
        if ($this->model->fetch_id(array($pk=>$this->id),null,true,$query))
          $q_list[] = $this->model;
      }
    }

    if ((count($q_list) == 0) || (!$q_list)){
      $this->response['type'] = 'error';
      $this->response['message'] = 'Cannot retrieve data';
      $this->response['code'] = 404;
    }else{
      $this->model->paginate($this->model);

      $this->response['code'] = 200;
      $this->response[get_class($this->model)] = array();
      foreach ($q_list as $k => $q_item) {
        $this->response[get_class($this->model)][] = $q_item->columns;
      }
    }

  }

  public function getPaginationLink(){
    return $this->model->pagination_link;
  }
}
?>
