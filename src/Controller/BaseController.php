<?php
/* API base controller
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This the blue print for any controller to be implemented in the future, it handles reponse filtering and session handling
 */
namespace Geekcow\FonyCore\Controller;

use Geekcow\FonyCore\Controller\CoreController;

use Geekcow\FonyCore\Utils\SessionUtils;
include_once "helpers/allow.php";

class BaseController extends CoreController
{
  protected $session;
  protected $validation_fail;

  public $response;
  public $pagination_link = "";

  public function __construct($configfile = MY_DOC_ROOT . "/core/config.ini"){
    parent::__construct($configfile);
    $this->response = array();
    $this->session = new SessionUtils($this->app_secret);
    if (!$this->session->validate_bearer_token($_SERVER['HTTP_Authorization'])){
      $this->validation_fail = true;
      $this->response = $this->session->response;
    }
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
