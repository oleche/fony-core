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

  private $app_secret;
  private $file_url;
  private $site_url;
  private $filename;


  // The constructor is private
  // to prevent initiation with outer code.
  private function __construct($configfile = MY_DOC_ROOT . "/src/config/config.ini")
  {
    $config = parse_ini_file($configfile);

    $this->app_secret = $config['app_secret'];
		$this->file_url = $config['file_url'];
		$this->site_url = $config['site_url'];
    $this->filename = $configfile;
  }

  public function getFileUrl(){
    return $this->file_url;
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

  // The object is created from within the class itself
  // only if the class has no instance.
  public static function getInstance($configfile = MY_DOC_ROOT . "/src/config/config.ini")
  {
    if (self::$instance == null)
    {
      self::$instance = new SessionUtils($configfile);
    }

    return self::$instance;
  }
}
