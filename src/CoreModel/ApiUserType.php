<?php
namespace Geekcow\FonyCore\CoreModel;

use \Geekcow\Dbcore\Entity;

class ApiUserType extends Entity{
  private $api_user_type = [
      'id' => [ 'type' => 'int', 'unique' => true, 'pk' => true ],
      'name' => [ 'type' => 'string', 'length' => 32, 'unique' => true ],
      'priority' => [ 'type' => 'int', 'unique' => true ],
      'scope' => [ 'type' => 'string', 'length' => 45, 'unique' => true ]
  ];

  public function __construct($configfile = __DIR__ . "/src/config/config.ini"){
    parent::__construct($this->api_user_type, get_class($this), $configfile);
  }
}

?>
