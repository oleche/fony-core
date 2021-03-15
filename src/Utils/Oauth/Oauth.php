<?php

/* Token Utils
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Static methods for handling decoding and encoding operations
 */

namespace Geekcow\FonyCore\Utils\Oauth;

use Geekcow\FonyCore\Utils\AuthenticatorInterface;
use Geekcow\FonyCore\Utils\ConfigurationUtils;

/**
 * Class Oauth
 * @package Geekcow\FonyCore\Utils\Oauth
 */
class Oauth implements AuthenticatorInterface
{
    /**
     * @var
     */
    private $err;
    /**
     * @var OauthClient
     */
    private $client;
    /**
     * @var ConfigurationUtils|null
     */
    private $config;

    /**
     * @var
     */
    private $username;
    /**
     * @var
     */
    private $expiration;
    /**
     * @var
     */
    private $client_id;
    /**
     * @var
     */
    private $scopes;

    private $scope_level;

    /**
     * Oauth constructor.
     */
    public function __construct()
    {
        $this->config = ConfigurationUtils::getInstance();
        $this->client = new OauthClient();
    }

    /**
     * @param string $token
     * @return false
     */
    public function validateBasicToken($token = '')
    {
        $this->err = "Not implemented";
        return false;
    }

    /**
     * @param string $token
     * @return bool
     */
    public function validateBearerToken($token = '')
    {
        $params = array();
        $params["token"] = $token;
        if (
            $this->client->doPOST(
                $this->config->getAuthenticationValidateTokenEndpoint(),
                $params
            )
        ) {
            $response = json_decode($this->client->getResult(), true);
            if (!isset($response['active']) || !$response['active']) {
                $this->err = $response['message'];
                return false;
            }
            $this->username = $response['username'];
            $this->scopes = $response['scope'];
            $this->scope_level = $response['scope_level'];
            $this->client_id = $response['client_id'];
            $this->expiration = $response['exp'];
            return true;
        }
        $this->err = "Not completed";
        return false;
    }

    /**
     * @return mixed
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @return mixed
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @return mixed
     */
    public function getErr()
    {
        return $this->err;
    }

    public function getScopeLevel()
    {
        return $this->scope_level;
    }
}
