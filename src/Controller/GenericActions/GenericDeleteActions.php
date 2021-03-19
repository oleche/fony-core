<?php

namespace Geekcow\FonyCore\Controller\GenericActions;

use Geekcow\FonyCore\Controller\CoreActions;
use Geekcow\FonyCore\Controller\CoreActionsInterface;
use Geekcow\FonyCore\Controller\GenericOperations\GenericDelete;

class GenericDeleteActions extends CoreActions implements CoreActionsInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    public function default($id = null)
    {
        if (!$this->validateScope($this->session->session_scopes)) {
            return false;
        }

        $delete = new GenericDelete($this->model, $this->session, $id);
        if ($this->session->session_level > 1) {
            $delete->checkUser();
        }
        $delete->setUsernameKey($this->usernameKey);
        $delete->delete();
        $this->response = $delete->getResponse();
    }
}