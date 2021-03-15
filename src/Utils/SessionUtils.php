<?php

/* Session Utils
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Session support using internal token handling
 */

namespace Geekcow\FonyCore\Utils;

use Geekcow\FonyCore\Utils\Oauth\Oauth;

/**
 * Class SessionUtils
 * @package Geekcow\FonyCore\Utils
 */
class SessionUtils
{
    // Hold the class instance.
    /**
     * @var null
     */
    private static $instance = null;

    /**
     * @var string
     */
    public $username;
    /**
     * @var
     */
    public $session_scopes;
    public $session_level;
    /**
     * @var array
     */
    public $response;
    /**
     * @var AuthenticatorInterface
     */
    private $authenticator;

    /**
     * SessionUtils constructor.
     * @param $authenticator
     */
    public function __construct($authenticator)
    {
        $this->response = array();
        $this->username = '';
        $this->authenticator = $authenticator;
    }

    /**
     * @param $authenticator
     */
    public function setAuthenticator($authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * @param $token
     * @return bool
     */
    public function validateBearerToken($token)
    {
        if ($this->authenticator->validateBearerToken($token)) {
            $this->username = $this->authenticator->getUsername();
            $this->session_scopes = $this->authenticator->getScopes();
            $this->session_level = $this->authenticator->getScopeLevel();
            return true;
        } else {
            $this->response['type'] = 'error';
            $this->response['code'] = 401;
            $this->response['message'] = $this->authenticator->getErr();
            return false;
        }
    }

    /**
     * The object is created from within the class itself
     * only if the class has no instance.
     *
     * @param null $authenticator
     * @return SessionUtils|null
     */
    public static function getInstance($authenticator = null)
    {
        if (self::$instance == null) {
            if (is_null($authenticator)) {
                $authenticator = new Oauth();
            }
            self::$instance = new SessionUtils($authenticator);
        }

        return self::$instance;
    }
}
