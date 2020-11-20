<?php
/* Oauth Client
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Rest client for oauth operations
 */
namespace Geekcow\FonyCore\Utils\Oauth;

use Geekcow\FonyCore\Controller;

class OauthClient implements ApiMethods {

  private $client_id;
  private $client_secret;
  private $host;
  private $port;
  private $config;
  private $result;

  public function __construct(){
    $this->config = ConfigurationUtils::getInstance();
    $this->port = 80;
    $this->host = $this->config->getAuthenticationUrl();
    $this->client_id = $this->config->getUserClient();
    $this->client_secret = $this->config->getUserSecret();
  }

  public function doPOST($endpoint = '', $params = []){
    try{
      $ch = curl_init();
      $method = "POST";
      $url = "$this->host/$endpoint";

      curl_setopt($ch, CURLOPT_USERPWD, $this->client_id . ":" . $this->client_secret);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_PORT, $this->port);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

      $result = curl_exec($ch);
      curl_close($ch);

      $this->result = $result;

      return true;
    }catch(\Exception $e){
      $this->result = $e->getMessage();
      return false;
    }
  }

  public function doGET(){
    return false;
  }

  public function doPUT(){
    return false;
  }

  public function doDELETE(){
    return false;
  }
}

?>
