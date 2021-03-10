<?php

/* Oauth Client
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Rest client for oauth operations
 */

namespace Geekcow\FonyCore\Utils\Oauth;

use Geekcow\FonyCore\Controller\ApiMethods;
use Geekcow\FonyCore\Utils\ConfigurationUtils;

/**
 * Class OauthClient
 * @package Geekcow\FonyCore\Utils\Oauth
 */
class OauthClient implements ApiMethods
{

    /**
     * @var mixed
     */
    private $client_id;
    /**
     * @var mixed
     */
    private $client_secret;
    /**
     * @var mixed|string
     */
    private $host;
    /**
     * @var int
     */
    private $port;
    /**
     * @var ConfigurationUtils|null
     */
    private $config;
    /**
     * @var
     */
    private $result;

    /**
     * OauthClient constructor.
     */
    public function __construct()
    {
        $this->config = ConfigurationUtils::getInstance();
        $this->port = 80;
        $this->host = $this->config->getAuthenticationUrl();
        $this->client_id = $this->config->getUserClient();
        $this->client_secret = $this->config->getUserSecret();
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @return bool
     */
    public function doPOST($endpoint = '', $params = [])
    {
        try {
            $ch = curl_init();
            $method = "POST";
            $endpoint = str_replace('/', '', $endpoint);
            $url = "$this->host/$endpoint";

            curl_setopt($ch, CURLOPT_USERPWD, $this->client_id . ":" . $this->client_secret);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_PORT, $this->port);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

            $result = curl_exec($ch);
            curl_close($ch);

            $this->result = $result;

            return true;
        } catch (\Exception $e) {
            $this->result = $e->getMessage();
            return false;
        }
    }

    /**
     * @return false
     */
    public function doGET()
    {
        return false;
    }

    /**
     * @return false
     */
    public function doPUT()
    {
        return false;
    }

    /**
     * @return false
     */
    public function doDELETE()
    {
        return false;
    }
}
