<?php
/* API base controller
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This the blue print for any controller to be implemented in the future, it handles reponse filtering and session handling
 */
namespace Geekcow\FonyCore\Controller;

use Geekcow\FonyCore\Controller\CoreController;

use Geekcow\FonyCore\Utils\SessionUtils;
use Geekcow\FonyCore\Utils\ConfigurationUtils;

class BaseController extends CoreController
{
  protected $session;
  protected $validation_fail;
  protected $action_class;
  protected $action_verb;
  protected $action_id;

  public $response;
  public $pagination_link = "";

  public function __construct(){
    parent::__construct();
    $this->response = array();
    $this->session = new SessionUtils();
    $this->action_verb = 'default';
    if (!$this->session->validate_bearer_token($_SERVER['HTTP_Authorization'])){
      $this->validation_fail = true;
      $this->response = $this->session->response;
    }
  }

  protected function execute($strict = false){
    $this->action_class->setSession($this->session);
    $this->action_class->setRoles($this->allowed_roles);
    if ((int)method_exists($this->action_class, $this->action_verb) > 0) {
      if (!is_null($this->action_id)){
        $this->action_class->{$this->action_verb}($this->action_id);
      }else{
        $this->action_class->{$this->action_verb}();
      }
      $this->response = $this->action_class->response;
      $this->pagination_link = $this->action_class->pagination_link;
      return true;
    }else{
      if (!$strict){
        if ((int)method_exists($this->action_class, 'default') > 0) {
          if (!is_null($this->action_id)){
            $this->action_class->default($this->action_id);
          }else{
            $this->action_class->default();
          }
          $this->response = $this->action_class->response;
          $this->pagination_link = $this->action_class->pagination_link;
          return true;
        }
      }
    }
    $this->response = array();
    $this->response['type'] = 'error';
    $this->response['title'] = 'User';
    $this->response['code'] = 404;
    $this->response['message'] = "Invalid URL";
    return false;
  }

  protected function setExecutableClass($action_class){
    $this->action_class = $action_class;
  }

  protected function setActionVerb($action_verb){
    $this->action_verb = $action_verb;
  }

  protected function setActionId($action_id){
    $this->action_id = $action_id;
  }

  protected function filter_response($fields=array()){
    $filter = array_merge($fields, ['password']);
    foreach ($filter as $value) {
      $this->remove_from_array($this->response, $value);
    }
  }

  protected function filter_stuff(&$array, $filters=array()){
    $filter = array_merge($filters, ['password']);
    foreach ($filter as $value) {
      $this->remove_from_array($array, $value);
    }
  }

  function remove_from_array(&$a1, $a2) {
    foreach($a1 as $k => &$v) {
      if (is_array($v)){
        $this->remove_from_array($v, $a2);
      }
    }
    if (isset($a1[$a2])){
      unset($a1[$a2]);
    }
  }

  protected function fieldsCount($fields, $justOne = false){
    $count = 0;
    foreach ($fields as $value) {
      if (!is_array($value)){
        if ($value != null && trim($value) != ""){
          $count++;
          if ($justOne)
            return $count;
        }
      }else{
        $count += $this->fieldsCount($value, true);
      }
    }
    return $count;
  }
}

?>
