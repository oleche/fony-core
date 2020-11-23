<?php
/* Token Utils
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Static methods for handling decoding and encoding operations
 */
namespace Geekcow\FonyCore\Utils\Oauth;

use Geekcow\FonyCore\Utils\Oauth\OauthClient;
use Geekcow\FonyCore\Utils\Authenticator;
use Geekcow\FonyCore\Utils\ConfigurationUtils;
use Geekcow\FonyCore\Controller;

class Oauth implements Authenticator{
  private $err;
  private $client;
  private $config;

  private $username;
  private $expiration;
  private $client_id;
  private $scopes;

  public function __construct(){
    $this->config = ConfigurationUtils::getInstance();
    $this->client = new OauthClient();
  }

  public function validateBasicToken($token = '') {
    $this->err = "Not implemented";
    return false;
  }

  public function validateBearerToken($token = '') {
    $params = array();
    $params["token"] = $token;
    if ($this->client->doPOST(
      $this->config->getAuthenticationValidateTokenEndpoint(),
      $params
    )) {
      $response = json_decode($this->client->getResult(),true);
      if (!isset($response['active']) || !$response['active']){
        $this->err = $response['message'];
        return false;
      }
      $this->username = $response['username'];
      $this->scopes = $response['scope'];
      $this->client_id = $response['client_id'];
      $this->expiration = $response['exp'];
      return true;
    }
    $this->err = "Not completed";
    return false;
  }

  public function getScopes() {
    return $this->scopes;
  }

  public function getUsername(){
    return $this->username;
  }

  public function getClientId(){
    return $this->client_id;
  }

  public function getExpiration(){
    return $this->expiration;
  }
}

?>
