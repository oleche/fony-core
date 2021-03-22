<?php

/* API core controller
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: This is the Core class to be implemented in any controller
 */

namespace Geekcow\FonyCore\Controller;

use Geekcow\Dbcore\Entity;
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
     * @var Entity
     */
    private $model;

    /**
     * @var array
     */
    private $filter;

    /**
     * GenericController constructor.
     * @param bool $with_form
     */
    public function __construct($with_form = false)
    {
        parent::__construct($with_form);
        $this->allowed_roles = AllowCore::SYSTEM();
        $this->filter = array();
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
            if (!$this->validateScope($this->session->session_scopes)){
                return false;
            }

            if ($this->validateFields($this->request, $this->form_endpoint, 'POST')) {
                $create = new GenericCreate($this->model, $this->session, $this->request);
                $create->setUsernameKey($this->usernameKey);
                $create->create();
                $this->response = $create->getResponse();
            }
        }

        $this->filterResponse(
            $this->filter
        );
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
            if (!$this->validateScope($this->session->session_scopes)){
                return false;
            }

            $ident = null;
            if ((count($args) > 0) && (is_numeric($args[0]))) {
                $ident = $args[0];
            } else {
                $ident = $verb;
            }
            $get = new GenericGet($this->model, $this->session, $ident);
            if ($this->session->session_level > 1){
                $get->checkUser();
            }
            $get->setUsernameKey($this->usernameKey);
            $get->get();
            $this->response = $get->getResponse();
            $this->pagination_link = $get->getPaginationLink();
        }

        $this->filterResponse(
            $this->filter
        );
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
            if (!$this->validateScope($this->session->session_scopes)){
                return false;
            }

            if ($this->validateFields($this->request, $this->form_endpoint, 'PUT')) {
                $put = new GenericPut($this->model, $this->session, $verb);
                $put->setParameters($this->request);
                $put->setValidationExclusion($this->allowed_roles);
                if ($this->session->session_level > 1){
                    $put->checkUser();
                }
                $put->setUsernameKey($this->usernameKey);
                $put->put();
                $this->response = $put->getResponse();
            }
        }

        $this->filterResponse(
            $this->filter
        );
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
            if (!$this->validateScope($this->session->session_scopes)){
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
            $delete->setUsernameKey($this->usernameKey);
            $delete->delete();
            $this->response = $delete->getResponse();
        }

        $this->filterResponse(
            $this->filter
        );
    }

    /**
     * @param $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @param array $filter
     */
    public function setFilter(array $filter): void
    {
        $this->filter = $filter;
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
