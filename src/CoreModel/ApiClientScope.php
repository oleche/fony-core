<?php
namespace Geekcow\FonyCore\CoreModel;

use \Geekcow\Dbcore\Entity;

use Geekcow\FonyCore\CoreModel\ApiClient;
use Geekcow\FonyCore\CoreModel\ApiScope;

class ApiClientScope extends Entity{
  private $api_client_scope;

  public function __construct($configfile = __DIR__ . "/src/config/config.ini"){
    $this->api_client_scope = [
        'id_scope' => [ 'type' => 'string', 'length' => 32, 'pk' => true, 'foreign' => array('name', new ApiScope()) ],
        'id_client' => [ 'type' => 'string', 'length' => 32, 'pk' => true, 'foreign' => array('client_id', new ApiClient()) ]
    ];
    parent::__construct($this->api_client_scope, get_class($this), $configfile);
  }
}

?>
