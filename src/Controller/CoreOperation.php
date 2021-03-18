<?php

/* API user controller
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: User controller
 */

namespace Geekcow\FonyCore\Controller;

use Geekcow\Dbcore\Entity;
use Geekcow\FonyCore\Helpers\AllowCore;
use Geekcow\FonyCore\Utils\SessionUtils;

class CoreOperation
{
    /**
     * @var Entity
     */
    protected $model;
    /**
     * @var SessionUtils
     */
    protected $session;
    protected $usernameKey;
    protected $validationExclusion;
    protected $checkUser;
    protected $response;
    /**
     * @var array
     */
    protected $parameters;

    /**
     * CoreActions constructor.
     * @param Entity $model
     * @param SessionUtils $session
     * @param array $params
     */
    public function __construct($model, $session, $params = array())
    {
        $this->model = $model;
        $this->session = $session;
        $this->validationExclusion = null;
        $this->usernameKey = 'username';
        $this->checkUser = false;
        $this->response = array();
        $this->parameters = $params;
    }

    protected function validateUser($username)
    {
        if (
            !is_null($this->validationExclusion) &&
            AllowCore::isAllowed(
                $this->session->session_scopes,
                $this->validationExclusion
            )
        ) {
            return true;
        } else {
            if ($this->session->username != $username) {
                $this->response['type'] = 'error';
                $this->response['title'] = 'User';
                $this->response['message'] = 'Cannot show this information';
                $this->response['code'] = 401;
                return false;
            } else {
                return true;
            }
        }
    }

    public function setUsernameKey($key = 'username')
    {
        $this->usernameKey = $key;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setValidationExclusion($validationExclusion)
    {
        $this->validationExclusion = $validationExclusion;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    public function checkUser()
    {
        $this->checkUser = true;
    }

    protected function getClassName($class){
        $path = explode('\\', get_class($class));
        return array_pop($path);
    }
}
