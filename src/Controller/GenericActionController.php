<?php

namespace Geekcow\FonyCore\Controller;

use Geekcow\Dbcore\Entity;
use Geekcow\FonyCore\Controller\GenericActions\GenericDeleteActions;
use Geekcow\FonyCore\Controller\GenericActions\GenericGetActions;
use Geekcow\FonyCore\Controller\GenericActions\GenericPostActions;
use Geekcow\FonyCore\Controller\GenericActions\GenericPutActions;
use Geekcow\FonyCore\Helpers\AllowCore;
use Geekcow\FonyCore\Utils\HashTypes;

class GenericActionController extends BaseController implements ApiMethods
{
    /**
     * @var CoreActions
     */
    private $post_action;
    /**
     * @var CoreActions
     */
    private $get_action;
    /**
     * @var CoreActions
     */
    private $put_action;
    /**
     * @var CoreActions
     */
    private $delete_action;

    /**
     * GenericController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->post_action = new GenericPostActions();
        $this->get_action = new GenericGetActions();
        $this->put_action = new GenericPutActions();
        $this->delete_action = new GenericDeleteActions();
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
            if (!$this->validateScope($this->session->session_scopes)) {
                return false;
            }

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
                $this->delete_action->setSession($this->session);
                $this->delete_action->setRoles($this->allowed_roles);
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
     * @param array $filter
     */
    public function setPostAction($post_action, $filter = array()): void
    {
        $this->post_action = $post_action;
        $this->post_action->setFilter($filter);
    }

    /**
     * @param mixed $get_action
     * @param array $filter
     */
    public function setGetAction($get_action, $filter = array()): void
    {
        $this->get_action = $get_action;
        $this->get_action->setFilter($filter);
    }

    /**
     * @param mixed $put_action
     * @param array $filter
     */
    public function setPutAction($put_action, $filter = array()): void
    {
        $this->put_action = $put_action;
        $this->put_action->setFilter($filter);
    }

    /**
     * @param mixed $delete_action
     * @param array $filter
     */
    public function setDeleteAction($delete_action, $filter = array()): void
    {
        $this->delete_action = $delete_action;
        $this->delete_action->setFilter($filter);
    }

    /**
     * @param Entity $model
     */
    public function setModel(Entity $model): void
    {
        $this->post_action->setModel($model);
        $this->get_action->setModel($model);
        $this->put_action->setModel($model);
        $this->delete_action->setModel($model);
    }
}