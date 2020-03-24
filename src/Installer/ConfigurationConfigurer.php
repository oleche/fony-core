<?php
namespace Geekcow\FonyCore\Installer;

use Geekcow\FonyCore\Utils\ConfigurationUtils;

class ConfigurationConfigurer{
  private $fieldsMap;
  private $config;
  private $currentGroup;
  private $path;

  public function __construct($path){
    $this->fieldsMap = array();
    $this->config = ConfigurationUtils::getInstance($path);
    $this->path = $path;
  }

  public function changeGroup($group){
    $this->currentGroup = $group;
  }

  public function setField($key, $value){
    if (!isset($this->fieldsMap[$this->currentGroup])){
      $this->fieldsMap[$this->currentGroup] = array();
    }
    $this->fieldsMap[$this->currentGroup][$key] = $value;
  }

  public function export(){
    $this->config->fromArray($this->fieldsMap);
    $this->config->exportToFile($this->path);
  }

}

?>
