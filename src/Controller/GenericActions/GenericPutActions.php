<?php

namespace Geekcow\FonyCore\Controller\GenericActions;

use Geekcow\FonyCore\Controller\CoreActions;
use Geekcow\FonyCore\Controller\CoreActionsInterface;

class GenericPutActions extends CoreActions implements CoreActionsInterface
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
        // TODO: Implement default() method.
    }
}
