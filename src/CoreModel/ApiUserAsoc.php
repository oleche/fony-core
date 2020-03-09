<?php
namespace Geekcow\FonyCore\CoreModel;

use \Geekcow\Dbcore\Entity;
use Geekcow\FonyCore\Utils\ConfigurationUtils;
use Geekcow\FonyCore\CoreModel\ApiUser;
use Geekcow\FonyCore\CoreModel\ApiClient;

class ApiUserAsoc extends Entity{
  private $api_user_asoc;

  public function __construct($config_file = MY_DOC_ROOT . "/src/config/config.ini"){
    $config = ConfigurationUtils::getInstance($config_file);
    $this->api_user_asoc = [
      'username' => [ 'type' => 'string', 'length' => 70, 'pk' => true, 'foreign' => array('username', new ApiUser()) ],
      'client_id' => [ 'type' => 'string', 'length' => 32, 'pk' => true, 'foreign' => array('client_id', new ApiClient()) ]
    ];
    parent::__construct($this->api_user_asoc, get_class($this), $config->getFilename());
  }
}

?>
