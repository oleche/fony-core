<?php

namespace Geekcow\FonyCore\Controller\GenericActions;

use Geekcow\FonyCore\Controller\CoreActions;
use Geekcow\FonyCore\Controller\CoreActionsInterface;
use Geekcow\FonyCore\Controller\GenericOperations\GenericCreate;

class GenericPostActions extends CoreActions implements CoreActionsInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    public function default()
    {
        if (!$this->validateScope($this->session->session_scopes)) {
            return false;
        }

        if ($this->validateFields($this->request, $this->form_endpoint, 'POST')) {
            $create = new GenericCreate($this->model, $this->session);
            $create->create();
            $this->response = $create->getResponse();
        }
    }
}