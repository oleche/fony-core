<?php
namespace Geekcow\FonyCore\CoreModel;

use \Geekcow\Dbcore\Entity;
use Geekcow\FonyCore\Utils\ConfigurationUtils;
use Geekcow\FonyCore\CoreModel\ApiUserType;

class ApiUser extends Entity{
  private $api_user;

  public function __construct($config_file = MY_DOC_ROOT . "/src/config/config.ini"){
    $config = ConfigurationUtils::getInstance($config_file);
    $this->api_user = [
        'username' => [ 'type' => 'string', 'length' => 70, 'unique' => true, 'pk' => true ],
        'name' => [ 'type' => 'string', 'length' => 45, 'unique' => true ],
        'lastname' => [ 'type' => 'string', 'length' => 45, 'unique' => true ],
        'email' => [ 'type' => 'string', 'length' => 70, 'unique' => true ],
        'avatar' => [ 'type' => 'text', 'nullable' => true ],
        'avatar_path' => [ 'type' => 'text', 'nullable' => true ],
        'phone' => [ 'type' => 'string', 'length' => 32 ],
        'password' => [ 'type' => 'string', 'length' => 64 ],
        'enabled' => [ 'type' => 'boolean'],
        'verified' => [ 'type' => 'boolean'],
        'verification' => [ 'type' => 'string', 'length' => 32 ],
        'created_at' => [ 'type' => 'datetime' ],
        'updated_at' => [ 'type' => 'datetime' ],
        'type' => [ 'type' => 'int', 'foreign' => array('id', new ApiUserType())]
    ];
    parent::__construct($this->api_user, get_class($this), $config->getFilename());
  }
}

?>
