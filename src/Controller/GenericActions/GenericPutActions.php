<?php

namespace Geekcow\FonyCore\Controller\GenericActions;

use Geekcow\FonyCore\Controller\CoreActions;
use Geekcow\FonyCore\Controller\CoreActionsInterface;
use Geekcow\FonyCore\Controller\GenericOperations\GenericPut;

class GenericPutActions extends CoreActions implements CoreActionsInterface
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

        if ($this->validateFields($this->request, $this->form_endpoint, 'PUT')) {
            $put = new GenericPut($this->model, $this->session, $id);
            if ($this->session->session_level > 1){
                $put->checkUser();
            }
            $put->setUsernameKey($this->usernameKey);
            $put->setParameters($this->request);
            $put->setValidationExclusion($this->allowed_roles);
            $put->put();
            $this->response = $put->getResponse();
        }
    }
}
