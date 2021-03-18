<?php

/* API core routing base
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This is the class where the initial and basic endpoints routing are taking place
 */

namespace Geekcow\FonyCore;

use Geekcow\FonyCore\Controller\GenericController;
use Geekcow\FonyCore\Helpers\AllowCore;
use Geekcow\FonyCore\Utils\ConfigurationUtils;

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
    protected $allowed_core_roles;

    public function __construct($URI, $origin, $config_file = MY_DOC_ROOT . "/src/config/config.ini")
    {
        parent::__construct($URI, $origin);

        $this->config_file = ConfigurationUtils::getInstance($config_file);
        $this->exclude_core_actions = false;
        $this->allowed_core_roles = AllowCore::SYSTEM();

        switch ($this->endpoint) {
            default:
                $this->core_action = new GenericController($this->config_file);
                $this->core_action->setRequest($this->request);
                break;
        }
    }

    public function setAllowedCoreRoles($role)
    {
        $this->allowed_core_roles = $role;
        $this->core_action->setAllowedRoles($role);
    }

    public function setAllowedRoles($role)
    {
        $this->action->setAllowedRoles($role);
    }
}
