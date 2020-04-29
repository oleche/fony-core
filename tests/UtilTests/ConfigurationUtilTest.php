<?php
declare(strict_types=1);

namespace Geekcow\FonyCore;

use PHPUnit\Framework\TestCase;
use \Geekcow\FonyCore\Utils\ConfigurationUtils;

final class ConfigurationUtilsTest extends TestCase {
  public function testCanBeCreated(): void {
    $config = ConfigurationUtils::getInstance(dirname(__DIR__, 1)."/resources/config.ini");
    $this->assertInstanceOf(
      ConfigurationUtils::class,
      $config
    );
  }

  public function testCanGenerateAQuery(): void {
    $config = ConfigurationUtils::getInstance(dirname(__DIR__, 1)."/resources/config.ini");
    $name = $config->getAppName();
    $this->assertEquals(
      $name,
      "fony-project"
    );
  }
}
