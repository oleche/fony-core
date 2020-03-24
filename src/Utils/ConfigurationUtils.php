<?php
/* Configuration Utils
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Configuration support for unified config file handling
 */
namespace Geekcow\FonyCore\Utils;

class ConfigurationUtils {
  // Hold the class instance.
  private static $instance = null;

  private $app_name;
  private $app_secret;
  //default client that allows login
  private $user_client;
  private $user_secret;
  private $file_path;
  private $file_url;
  private $site_url;
  private $filename;
  //other custom fields
  private $fieldsMap;

  // The constructor is private
  // to prevent initiation with outer code.
  private function __construct($configfile = MY_DOC_ROOT . "/src/config/config.ini")
  {
    if (($config = @parse_ini_file($configfile)) == false){
      $this->app_name = "NO CONFIG FILE FOUND";
    }else{
      $this->app_name = $config['fony.app_name'];
      $this->app_secret = $config['fony.app_secret'];
      $this->user_client = $config['fony.user_client'];
      $this->user_secret = $config['fony.user_secret'];
      $this->file_path = $config['fony.file_path'];
      $this->file_url = $config['fony.file_url'];
      $this->site_url = $config['fony.site_url'];
    }

    $this->filename = $configfile;
    $this->fieldsMap = array();
  }

  public function getFileUrl(){
    return $this->file_url;
  }

  public function getFilePath(){
    return $this->file_path;
  }

  public function getSiteUrl(){
    return $this->site_url;
  }

  public function getFilename(){
    return $this->filename;
  }

  public function getAppSecret(){
    return $this->app_secret;
  }

  public function getAppName(){
    return $this->app_name;
  }

  public function getUserClient(){
    return $this->user_client;
  }

  public function getUserSecret(){
    return $this->user_secret;
  }

  public function fromArray($parameters = array()){
    $this->fieldsMap = $parameters;
  }

  public function exportToFile($path){
    if (!isset($this->fieldsMap['fony'])){
      $this->fieldsMap['fony'] = array();
    }
    if (!isset($this->fieldsMap['fony']['fony.app_name'])){
      $this->fieldsMap['fony']['fony.app_name'] = $this->app_name ;
    }else{
      $this->app_name = $this->fieldsMap['fony']['fony.app_name'] ;
    }
    if (!isset($this->fieldsMap['fony']['fony.app_secret'])){
      $this->fieldsMap['fony']['fony.app_secret'] = $this->app_secret ;
    }else{
      $this->app_secret = $this->fieldsMap['fony']['fony.app_secret'] ;
    }
    if (!isset($this->fieldsMap['fony']['fony.user_client'])){
      $this->fieldsMap['fony']['fony.user_client'] = $this->user_client ;
    }else{
      $this->user_client = $this->fieldsMap['fony']['fony.user_client'] ;
    }
    if (!isset($this->fieldsMap['fony']['fony.user_secret'])){
      $this->fieldsMap['fony']['fony.user_secret'] = $this->user_secret ;
    }else{
      $this->user_secret = $this->fieldsMap['fony']['fony.user_secret'] ;
    }
    if (!isset($this->fieldsMap['fony']['fony.file_url'])){
      $this->fieldsMap['fony']['fony.file_url'] = $this->file_url ;
    }else{
      $this->file_url = $this->fieldsMap['fony']['fony.file_url'] ;
    }
    if (!isset($this->fieldsMap['fony']['fony.file_path'])){
      $this->fieldsMap['fony']['fony.file_path'] = $this->file_path ;
    }else{
      $this->file_path = $this->fieldsMap['fony']['fony.file_path'] ;
    }
    if (!isset($this->fieldsMap['fony']['fony.site_url'])){
      $this->fieldsMap['fony']['fony.site_url'] = $this->site_url ;
    }else{
      $this->site_url = $this->fieldsMap['fony']['fony.site_url'] ;
    }

    if (!file_exists(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }
    $fp = fopen($path,"wb");
    fwrite($fp,"");
    fclose($fp);

    $this->writeIniFile($this->fieldsMap, $path, true);

  }

  private function writeIniFile($assoc_arr, $path, $has_sections)
  {
    $content = '';

    if (!$handle = fopen($path, 'w'))
      return FALSE;

    $this->writeIniFileR($content, $assoc_arr, $has_sections);

    if (!fwrite($handle, $content))
      return FALSE;

    fclose($handle);
    return TRUE;
  }

  private function writeIniFileR(&$content, $assoc_arr, $has_sections)
  {
    foreach ($assoc_arr as $key => $val) {
      if (is_array($val)) {
        if($has_sections) {
          $content .= "[$key]\n";
          $this->writeIniFileR($content, $val, false);
        } else {
          foreach($val as $iKey => $iVal) {
            if (is_int($iKey))
              $content .= $key ."[] = $iVal\n";
            else
              $content .= $key ."[$iKey] = $iVal\n";
          }
        }
      } else {
        $content .= "$key = $val\n";
      }
    }
  }

  // The object is created from within the class itself
  // only if the class has no instance.
  public static function getInstance($configfile = MY_DOC_ROOT . "/src/config/config.ini")
  {
    if (self::$instance == null)
    {
      self::$instance = new ConfigurationUtils($configfile);
    }

    return self::$instance;
  }
}
