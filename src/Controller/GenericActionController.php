<?php

namespace Geekcow\FonyCore\Controller;

use Geekcow\FonyCore\Helpers\AllowCore;

class GenericActionController extends BaseController implements ApiMethods
{
    private $post_action;
    private $get_action;
    private $put_action;
    private $delete_action;

    /**
     * GenericController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->allowed_roles = AllowCore::SYSTEM();
    }

    public function doPOST($args = array(), $verb = null)
    {
        if (!$this->validation_fail) {
            if (!$this->validateScope($this->session->session_scopes)) {
                return false;
            }

            $this->executeActionFlow($args, $verb, $this->post_action);
        }

        $this->filterResponse(
            $this->get_action->getFilter()
        );
    }

    public function doGET($args = array(), $verb = null)
    {
        if (!$this->validation_fail) {
            if (!$this->validateScope($this->session->session_scopes)) {
                return false;
            }

            $this->executeActionFlow($args, $verb, $this->get_action);
        }

        $this->filterResponse(
            $this->get_action->getFilter()
        );
    }

    public function doPUT($args = array(), $verb = null, $file = null)
    {
        if (!$this->validation_fail) {
            $this->executeActionFlow($args, $verb, $this->put_action, $file);
        }

        $this->filterResponse(
            $this->get_action->getFilter()
        );
    }

    public function doDELETE($args = array(), $verb = null)
    {
        if (!$this->validation_fail) {
            if (!$this->validateScope($this->session->session_scopes)) {
                return false;
            }

            if (is_array($args) && empty($args)) {
                $this->setExecutableClass($this->delete_action);
                $this->setActionVerb($verb);
                $this->execute();
            } else {
                $this->response['type'] = 'error';
                $this->response['title'] = 'User';
                $this->response['code'] = 404;
                $this->response['message'] = "Invalid URL";
            }
        }

        $this->filterResponse(
            $this->get_action->getFilter()
        );
    }

    /**
     * @param mixed $post_action
     */
    public function setPostAction($post_action): void
    {
        $this->post_action = $post_action;
    }

    /**
     * @param mixed $get_action
     */
    public function setGetAction($get_action): void
    {
        $this->get_action = $get_action;
    }

    /**
     * @param mixed $put_action
     */
    public function setPutAction($put_action): void
    {
        $this->put_action = $put_action;
    }

    /**
     * @param mixed $delete_action
     */
    public function setDeleteAction($delete_action): void
    {
        $this->delete_action = $delete_action;
    }

    /**
     * @param array $args
     * @param $verb
     */
    private function executeActionFlow(array $args, $verb, $action, $file = null): void
    {
        if (!is_null($file)){
            $action->setFile($file);
        }
        $action->setSession($this->session);
        $action->setRoles($this->allowed_roles);
        $this->setExecutableClass($action);
        $strict = false;
        if (is_array($args) && empty($args)) {
            $this->setActionId($verb);
        } else {
            $this->setActionId($verb);
            $this->setActionVerb($args[0]);
            $strict = true;
        }
        $this->execute($strict);
    }
}