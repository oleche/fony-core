<?php
/* API core routing base
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This is the class where the initial and basic endpoints routing are taking place
 */
namespace Geekcow\FonyCore;

use Geekcow\FonyCore\API;
use Geekcow\FonyCore\Controller\GenericController;

/**
 * CORE API Implementation
 *
 * Serves as the API router for the controllers according the http method
 *
 * FOR ANY ACTION AND RESOURCE MUST CREATE THE REQUIRED CALLS
 *  protected function demo(){
 *    $this->action->setModel(new SiteState());
 *    switch ($this->method) {
 *      case 'POST':
 *        $this->action->setFormEndpoint('v1/demo');
 *        break;
 *      case 'PUT':
 *        $this->action->setFormEndpoint('v1/demo/:id');
 *        break;
 *      default:
 *    }
 *    return $this->doRegulaCall();
 *  }
 *
 *  A SIMPLE IMPLEMENTATION WITHOUT FORM CUSTOMIZATION
 *  protected function demo2(){
 *    return $this->doRegulaCall();
 *  }
 *
 */
class FonyApi extends API
{
  protected $config_file;
  protected $exclude_core_actions;

  public function __construct($request, $origin, $config_file = MY_DOC_ROOT . "/src/config/config.ini") {
    parent::__construct($request, $origin);

    $this->config_file = $config_file;
    $this->exclude_core_actions = false;

    switch($this->endpoint){
      case 'user':
        $this->core_action = new GenericController($this->config_file);
        // $this->core_action = new UserController($this->config_file);
        $this->core_action->setRequest($request);
        break;
      case 'client':
        $this->core_action = new GenericController($this->config_file);
        $this->core_action->setRequest($request);
        break;
      case 'scope':
        $this->core_action = new GenericController($this->config_file);
        $this->core_action->setRequest($request);
        break;
      case 'auth':
        $this->core_action = new GenericController($this->config_file);
        // $this->core_action = new AuthController($this->config_file);
        $this->core_action->setRequest($request);
        break;
      default:
        $this->core_action = new GenericController($this->config_file);
        $this->core_action->setRequest($request);
        break;
    }
  }

  /**
   * Executes the authentication endpoint.
   *
   * @return JSON Authenticated response with token
   *
   */
  protected function auth(){
    return $this->_executesCall(true);
    // switch ($this->method) {
    //  case 'POST':
    //    $this->core_action->doPost($_SERVER['HTTP_Authorization'], $_POST, $this->method);
    //    $this->response_code = $this->core_action->response['code'];
    //    return $this->core_action->response;
    //    break;
    //  case 'OPTIONS':
    //    exit(0);
    //    break;
    //  default:
    //    $this->response_code = 405;
    //    return "Invalid method";
    //    break;
    // }
  }

  /**
   * Executes the user endpoint.
   *
   * @return JSON User response
   *
   */
  protected function user(){
    if ($this->exclude_core_actions){
      $this->response_code = 405;
      return "Invalid method";
    }
    return $this->_executesCall(true);
  }

  /**
   * Executes the client endpoint.
   *
   * @return JSON Client response
   *
   */
  protected function client(){
    if ($this->exclude_core_actions){
      $this->response_code = 405;
      return "Invalid method";
    }
    return $this->_executesCall(true);
  }

  /**
   * Executes the scope endpoint.
   *
   * @return JSON Scope response
   *
   */
  protected function scope(){
    if ($this->exclude_core_actions){
      $this->response_code = 405;
      return "Invalid method";
    }
    return $this->_executesCall(true);
  }
}
