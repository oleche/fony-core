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

class UserCreation {


  public static function createCore(Event $event){
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
    echo 'Configuration script' . PHP_EOL;
    echo '====================' . PHP_EOL;
    echo PHP_EOL;

    echo 'Write the location of your fony configuration file: ['.__DIR__.'/src/config/config.ini]: ';
    $config = UserCreation::getInput(__DIR__ . "/src/config/config.ini");
    echo PHP_EOL;

    echo 'Write the administrator email: [admin@test.com]: ';
    $username = UserCreation::getInput("admin@test.com");
    echo PHP_EOL;

    $password = "";
    while ($password == ""){
      echo 'Write the administrator password: ';
      $password = UserCreation::getInput();
    }
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
    return $response;
  }
}

?>
