<?php
namespace Geekcow\FonyCore\CoreModel;

use \Geekcow\Dbcore\Entity;

class ApiFieldType extends Entity{
  private $api_field_type = [
      'id' => [ 'type' => 'int', 'pk' => true ],
      'name' => [ 'type' => 'string', 'length' => 75 ],
      'regex' => [ 'type' => 'string', 'length' => 800 ]
  ];

  public function __construct($configfile = MY_DOC_ROOT . "/src/config/config.ini"){
    parent::__construct($this->api_field_type, get_class($this), $configfile);
  }
}

?>
