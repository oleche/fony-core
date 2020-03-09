<?php
namespace Geekcow\FonyCore\CoreModel;

use \Geekcow\Dbcore\Entity;
use Geekcow\FonyCore\Utils\ConfigurationUtils;

class ApiFieldType extends Entity{
  private $api_field_type = [
      'id' => [ 'type' => 'int', 'pk' => true ],
      'name' => [ 'type' => 'string', 'length' => 75 ],
      'regex' => [ 'type' => 'string', 'length' => 800 ]
  ];

  public function __construct($config_file = MY_DOC_ROOT . "/src/config/config.ini"){
    $config = ConfigurationUtils::getInstance($config_file);
    parent::__construct($this->api_field_type, get_class($this), $config->getFilename());
  }
}

?>
