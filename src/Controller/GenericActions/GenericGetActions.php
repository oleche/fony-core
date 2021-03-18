<?php

namespace Geekcow\FonyCore\Controller\GenericActions;

use Geekcow\FonyCore\Controller\CoreActions;
use Geekcow\FonyCore\Controller\CoreActionsInterface;
use Geekcow\FonyCore\Controller\GenericOperations\GenericGet;

class GenericGetActions extends CoreActions implements CoreActionsInterface
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

        $get = new GenericGet($this->model, $this->session, $id);
        if ($this->session->session_level > 1){
            $get->checkUser();
        }
        $get->get();
        $this->response = $get->getResponse();
        $this->pagination_link = $get->getPaginationLink();
    }
}
