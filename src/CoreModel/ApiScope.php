<?php
namespace Geekcow\FonyCore\CoreModel;

use \Geekcow\Dbcore\Entity;

class ApiScope extends Entity{
  private $api_scope = [
      'name' => [ 'type' => 'string', 'length' => 45, 'unique' => true, 'pk' => true ],
      'level' => [ 'type' => 'int' ],
      'priority' => [ 'type' => 'int' ]
  ];

  public function __construct($configfile = MY_DOC_ROOT . "/core/config.ini"){
    parent::__construct($this->api_scope, get_class($this), $configfile);
  }
}

?>
