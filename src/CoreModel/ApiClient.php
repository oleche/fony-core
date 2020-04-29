<?php
namespace Geekcow\FonyCore\CoreModel;

use \Geekcow\Dbcore\Entity;
use Geekcow\FonyCore\Utils\ConfigurationUtils;
use Geekcow\FonyCore\CoreModel\ApiUser;

class ApiClient extends Entity{
  private $api_client;

  public function __construct($config_file = MY_DOC_ROOT . "/src/config/config.ini"){
    $config = ConfigurationUtils::getInstance($config_file);
    $this->api_client = [
        'client_id' => [ 'type' => 'string', 'length' => 64, 'pk' => true ],
        'client_secret' => [ 'type' => 'string', 'length' => 64 ],
        'email' => [ 'type' => 'string' ],
        'user_id' => [ 'type' => 'string', 'length' => 70, 'foreign' => array('username', new ApiUser()) ],
        'created_at' => [ 'type' => 'datetime' ],
        'updated_at' => [ 'type' => 'datetime' ],
        'enabled' => [ 'type' => 'boolean' ],
        'asoc' => [ 'type' => 'boolean' ]
    ];
    parent::__construct($this->api_client, get_class($this), $config->getFilename());
  }
}

?>
