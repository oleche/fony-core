<?php

/* API core controller
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This is the Core class to be implemented in any controller
 */

namespace Geekcow\FonyCore\Controller;

use Geekcow\FonyCore\Controller\GenericOperations\GenericCreate;
use Geekcow\FonyCore\Controller\GenericOperations\GenericDelete;
use Geekcow\FonyCore\Controller\GenericOperations\GenericGet;
use Geekcow\FonyCore\Controller\GenericOperations\GenericPut;
use Geekcow\FonyCore\Helpers\AllowCore;

/**
 * Class GenericController
 * @package Geekcow\FonyCore\Controller
 */
class GenericController extends BaseController implements ApiMethods
{
    /**
     * @var
     */
    private $model;

    /**
     * GenericController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->allowed_roles = AllowCore::ADMINISTRATOR();
    }

    //USUALLY TO CREATE

    /**
     * @param array $args
     * @param null $verb
     * @return false
     */
    public function doPOST($args = array(), $verb = null)
    {
        if (!$this->validation_fail) {
            if (!AllowCore::isAllowed($this->session->session_scopes, $this->allowed_roles)) {
                $this->response = AllowCore::denied($this->session->session_scopes);
                return false;
            }

            if ($this->validateFields($_POST, $this->form_endpoint, 'POST')) {
                $create = new GenericCreate($this->model, $this->session);
                $create->create();
                $this->response = $create->getResponse();
            }
        }
    }

    //USUALLY TO READ INFORMATION

    /**
     * @param array $args
     * @param null $verb
     * @return false
     */
    public function doGET($args = array(), $verb = null)
    {
        if (!$this->validation_fail) {
            if (!AllowCore::isAllowed($this->session->session_scopes, $this->allowed_roles)) {
                $this->response = AllowCore::denied($this->session->session_scopes);
                return false;
            }

            $ident = null;
            if ((count($args) > 0) && (is_numeric($args[0]))) {
                $ident = $args[0];
            } else {
                $ident = $verb;
            }
            $get = new GenericGet($this->model, $this->session, $ident);
            $get->get();
            $this->response = $get->getResponse();
            $this->pagination_link = $get->getPaginationLink();
        }
    }

    //TEND TO HAVE MULTIPLE METHODS

    /**
     * @param array $args
     * @param null $verb
     * @param null $file
     * @return false
     */
    public function doPUT($args = array(), $verb = null, $file = null)
    {
        if (!$this->validation_fail) {
            if (!AllowCore::isAllowed($this->session->session_scopes, $this->allowed_roles)) {
                $this->response = AllowCore::denied($this->session->session_scopes);
                return false;
            }
            if ($this->validateFields($_POST, $this->form_endpoint, 'PUT')) {
                $put = new GenericPut($this->model, $this->session, $verb);
                $put->setValidationExclusion($this->allowed_roles);
                $put->put();
                $this->response = $put->getResponse();
            }
        }
    }

    //DELETES ONE SINGLE ENTRY

    /**
     * @param array $args
     * @param null $verb
     * @return false
     */
    public function doDELETE($args = array(), $verb = null)
    {
        if (!$this->validation_fail) {
            if (!AllowCore::isAllowed($this->session->session_scopes, $this->allowed_roles)) {
                $this->response = AllowCore::denied($this->session->session_scopes);
                return false;
            }

            $ident = null;
            if ((count($args) > 0) && (is_numeric($args[0]))) {
                $ident = $args[0];
            } else {
                $ident = $verb;
            }

            $delete = new GenericDelete($this->model, $ident, $this->session);
            $delete->setValidationExclusion($this->allowed_roles);
            $delete->delete();
            $this->response = $delete->getResponse();
        }
    }

    /**
     * @param $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @param $args
     * @param $verb
     */
    private function validateArgsAndVerb($args, $verb)
    {
        //TODO: Pending to add a validation for args and verbs
    }
}
