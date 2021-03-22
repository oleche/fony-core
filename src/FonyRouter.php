<?php

/* API core routing base
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This is the class where the initial and basic endpoints routing are taking place
 */

namespace Geekcow\FonyCore;

use Geekcow\FonyCore\Controller\BaseController;
use Geekcow\FonyCore\Controller\GenericController;
use Geekcow\FonyCore\CoreModel\ApiForm;
use Geekcow\FonyCore\Helpers\AllowCore;
use Geekcow\FonyCore\Utils\ConfigurationUtils;
use Geekcow\FonyCore\Utils\UriUtils;

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
abstract class FonyRouter implements FonyRouterInterface
{
    /**
     * @var ConfigurationUtils|null
     */
    protected $config_file;
    protected $exclude_core_actions;
    protected $allowed_core_roles;
    /**
     * Property: action
     * Linked to the action controller of the resource
     * @var BaseController
     */
    protected $action;

    /**
     * Property: core_action
     * Linked to the action controller of the core components
     * @var BaseController
     */
    protected $core_action;

    protected $response_code;

    protected $request;

    protected $endpoint;

    public $headers;
    public $method;
    public $args;
    public $verb;
    public $file;

    /**
     * FonyRouter constructor.
     * @param string $config_file
     */
    public function __construct($config_file = MY_DOC_ROOT . "/src/config/config.ini")
    {
        $this->config_file = ConfigurationUtils::getInstance($config_file);
        $this->exclude_core_actions = false;
        $this->allowed_core_roles = AllowCore::SYSTEM();
        $this->request = array();
        $this->headers = array();
    }

    public function prestageEndpoints($endpoint, $request){
        $this->endpoint = $endpoint;
        $this->request = $request;

        switch ($this->endpoint) {
            case 'api-forms':
                $this->core_action = new GenericController();
                $this->core_action->setRequest($this->request);
                $this->core_action->setModel(new ApiForm());
                $this->setAllowedCoreRoles(AllowCore::SYSTEM());
                break;
            default:
                $this->core_action = new GenericController();
                $this->core_action->setRequest($this->request);
                break;
        }
    }

    private function setAllowedCoreRoles($role)
    {
        $this->allowed_core_roles = $role;
        $this->core_action->setAllowedRoles($role);
    }

    protected function setAllowedRoles($role)
    {
        $this->action->setAllowedRoles($role);
    }

    public function apiForms(){
        return $this->executesCall(true);
    }

    /**
     * Executes a standard processing of the allowed http methods based on the GenericController structure.
     *
     * @return array|OBJECT|string
     *
     */
    protected function executesCall($coreAction = false)
    {
        if ($coreAction) {
            $this->action = $this->core_action;
        }

        $this->action->setFormEndpoint(UriUtils::processUri($this->endpoint, $this->args, $this->verb));

        switch ($this->method) {
            case 'POST':
                $this->action->doPost($this->args, $this->verb);
                $this->response_code = $this->action->response['code'];
                return $this->action->response;
            case 'GET':
                $this->action->doGet($this->args, $this->verb);
                $this->addHeader($this->action->pagination_link);
                $this->response_code = $this->action->response['code'];
                return $this->action->response;
            case 'PUT':
                parse_str($this->file, $_POST);
                $this->action->doPut($this->args, $this->verb, $this->file);
                $this->response_code = $this->action->response['code'];
                return $this->action->response;
            case 'DELETE':
                $this->action->doDelete($this->args, $this->verb);
                $this->response_code = $this->action->response['code'];
                return $this->action->response;
            case 'OPTIONS':
                exit(0);
            default:
                $this->response_code = 405;
                return "Invalid Method";
        }
    }

    /**
     * @return mixed
     */
    public function getResponseCode()
    {
        return $this->response_code;
    }

    public function addHeader($header)
    {
        $this->headers[] = $header;
    }
}
