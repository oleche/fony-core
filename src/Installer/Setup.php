<?php
/* Auth Utils
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Authentication support for token generation and general authentication
 */
namespace Geekcow\FonyCore\Installer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Composer\Factory;
use Composer\IO\NullIO;
use Geekcow\FonyCore\Installer\ConfigurationConfigurer;

class Setup {


  public static function init(Event $event){
    $composer = Factory::create(new NullIo(), './composer.json', false);

    $logo = "  __
 / _| ___  _ __  _   _        ___ ___  _ __ ___
| |_ / _ \| '_ \| | | |_____ / __/ _ \| '__/ _ \
|  _| (_) | | | | |_| |_____| (_| (_) | | |  __/
|_|  \___/|_| |_|\__, |      \___\___/|_|  \___|
                 |___/                          ";

    echo $logo . PHP_EOL;
    echo $composer->getPackage()->getName() . PHP_EOL;
    echo "version: " . $composer->getPackage()->getVersion() . PHP_EOL;
    echo 'Installation script' . PHP_EOL;
    echo '====================' . PHP_EOL;
    echo PHP_EOL;

    echo 'Name of your project: [fony-project]: ';
    $project_name = Setup::getInput("fony-project");
    echo PHP_EOL;

    echo 'Path for your fony configuration file: ['.__DIR__.'/src/config/config.ini]: ';
    $config = Setup::getInput(__DIR__ . "/src/config/config.ini");
    echo PHP_EOL;

    $configurer = new ConfigurationConfigurer($config);
    $configurer->changeGroup('fony');
    $configurer->setField('fony.app_name', $project_name);

    echo 'URL of your API: [api.test.com]: ';
    $site_url = Setup::getInput("api.test.com");
    $configurer->changeGroup('fony');
    $configurer->setField('fony.site_url', $site_url);
    echo PHP_EOL;

    echo 'URL of your API Assets: [assets.test.com]: ';
    $file_url = Setup::getInput("assets.test.com");
    $configurer->changeGroup('fony');
    $configurer->setField('fony.file_url', $file_url);
    echo PHP_EOL;

    echo 'Internal path of your API assets: ['.__DIR__.'/assets/]: ';
    $file_path = Setup::getInput(__DIR__.'/assets/');
    $configurer->changeGroup('fony');
    $configurer->setField('fony.file_path', $file_path);
    echo PHP_EOL;

    echo 'Database information' . PHP_EOL;
    echo 'Currently, fony-php only supports MySQL. Soon more databases will be supported' . PHP_EOL;
    echo '====================' . PHP_EOL;
    echo 'Server location: [localhost]: ';
    $db_server = Setup::getInput("localhost");
    echo PHP_EOL;
    echo 'DB username: [root]: ';
    $db_user = Setup::getInput("root");
    echo PHP_EOL;
    echo 'DB password: [r00t]: ';
    $db_password = Setup::getInput("r00t");
    echo PHP_EOL;
    echo 'database: ['.Setup::dashesToCamelCase($project_name).']: ';
    $database = Setup::getInput(Setup::dashesToCamelCase($project_name));
    echo PHP_EOL;
    $configurer->changeGroup('dbcore');
    $configurer->setField('dbcore.server', $db_server);
    $configurer->setField('dbcore.db_user', $db_user);
    $configurer->setField('dbcore.db_pass', $db_password);
    $configurer->setField('dbcore.database', $database);
    $configurer->setField('dbcore.ipp', 25);

    echo 'Application information' . PHP_EOL;
    echo '====================' . PHP_EOL;
    $secret = "";
    while ($secret == ""){
      echo 'Write the application secret word: ';
      $secret = Setup::getInput();
      $configurer->changeGroup('fony');
      $configurer->setField('fony.app_secret', $secret);
    }

    echo 'Write the administrator email: [admin@test.com]: ';
    $username = Setup::getInput("admin@test.com");
    echo PHP_EOL;

    $encodedUser = md5($username);
    $client = sha1($encodedUser.$username.date("Y-m-d H:i:s"));
		$secret = sha1($client.'SECRETKEY');
    $configurer->changeGroup('fony');
    $configurer->setField('fony.user_client', $client);
    $configurer->setField('fony.user_secret', $secret);

    $configurer->export();

    $password = "";
    while ($password == ""){
      echo 'Write the administrator password: ';
      $password = Setup::getInput();
    }
    echo PHP_EOL;

    echo 'Build default database (y/Y/n/N): [y]: ';
    $build_default = Setup::getInput("y");
    echo PHP_EOL;

    //var_dump($event->getArguments());
  }

  public static function getInput($default = ""){
    $response = "";
    $stdin = fopen('php://stdin', 'r');
    $response = fgets($stdin);
    fclose($stdin);
    if (trim($response) == ""){
      $response = $default;
    }
    return trim($response);
  }

  public static function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
  {

      $str = str_replace('-', '', ucwords($string, '-'));
      $str = str_replace(' ', '', ucwords($str, ' '));
      $str = str_replace('.', '', ucwords($str, '.'));

      if (!$capitalizeFirstCharacter) {
          $str = lcfirst($str);
      }

      return $str;
  }
}

?>
