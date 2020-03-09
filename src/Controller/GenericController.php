<?php
/* API core controller
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This is the Core class to be implemented in any controller
 */
namespace Geekcow\FonyCore\Controller;

use Geekcow\FonyCore\Controller\BaseController;
use Geekcow\FonyCore\Controller\ApiMethods;
use Geekcow\FonyCore\Helpers\AllowCore;
use Geekcow\FonyCore\Controller\GenericOperations\GenericCreate;
use Geekcow\FonyCore\Controller\GenericOperations\GenericGet;
use Geekcow\FonyCore\Controller\GenericOperations\GenericPut;
use Geekcow\FonyCore\Controller\GenericOperations\GenericDelete;
use Geekcow\FonyCore\Utils\ConfigurationUtils;

class GenericController extends BaseController implements ApiMethods
{
  private $model;

  public function __construct() {
		parent::__construct();
    $this->allowed_roles = AllowCore::ADMINISTRATOR();
	}

  //USUALLY TO CREATE
  public function doPOST($args = array(), $verb = null) {
    if (!$this->validation_fail)
    {
      if (!AllowCore::is_allowed($this->session->session_scopes, $this->allowed_roles)){
        $this->response = AllowCore::denied($this->session->session_scopes);
        return false;
      }

      if ($this->validate_fields($_POST, $this->form_endpoint, 'POST')){
        $create = new GenericCreate($this->model);
        $create->create($this->session->session_scopes);
        $this->response = $create->response;
      }
    }
  }

  //USUALLY TO READ INFORMATION
  public function doGET($args = array(), $verb = null) {
    if (!$this->validation_fail)
    {
      if (!AllowCore::is_allowed($this->session->session_scopes, $this->allowed_roles)){
        $this->response = AllowCore::denied($this->session->session_scopes);
        return false;
      }

      $ident = null;
      if ((count($args) > 0) && (is_numeric($args[0]))){
        $ident = $args[0];
      }else{
        $ident = $verb;
      }
      $get = new GenericGet($this->model, $this->session, $ident);
      $get->get();
      $this->response = $get->response;
      $this->pagination_link = $get->getPaginationLink();
    }
  }

  //TEND TO HAVE MULTIPLE METHODS
  public function doPUT($args = array(), $verb = null, $file = null) {
    if (!$this->validation_fail)
    {
      if (!AllowCore::is_allowed($this->session->session_scopes, $this->allowed_roles)){
        $this->response = AllowCore::denied($this->session->session_scopes);
        return false;
      }
      if ($this->validate_fields($_POST, $this->form_endpoint, 'PUT')){
        $put = new GenericPut($this->model, $this->session, $verb);
        $put->setValidationExclusion($this->allowed_roles);
        $put->put();
        $this->response = $put->response;
      }
    }
  }

  //DELETES ONE SINGLE ENTRY
  public function doDELETE($args = array(), $verb = null) {
    if (!$this->validation_fail)
    {
      if (!AllowCore::is_allowed($this->session->session_scopes, $this->allowed_roles)){
        $this->response = AllowCore::denied($this->session->session_scopes);
        return false;
      }

      $ident = null;
      if ((count($args) > 0) && (is_numeric($args[0]))){
        $ident = $args[0];
      }else{
        $ident = $verb;
      }

      $delete = new GenericDelete($this->model, $ident, $this->session);
      $delete->setValidationExclusion($this->allowed_roles);
      $delete->delete();
      $this->response = $delete->response;
    }
  }

  public function setModel($model){
    $this->model = $model;
  }

  private function validateArgsAndVerb($args, $verb){
    //TODO: Pending to add a validation for args and verbs
  }

}

?>
