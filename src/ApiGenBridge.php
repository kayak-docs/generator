<?php

namespace KayakDocs\Generator;

use Nette\Configurator;

class ApiGenBridge {

  /**
   * @var string[]
   */
  protected $scanner = [];

  /**
   * @var \Nette\DI\Container
   */
  protected $container;

  public function __construct($cacheDir, array $services = []) {
    $defaultServices = [
      'configuration' => 'ApiGen\Configuration\Configuration',
      'scanner' => 'ApiGen\Scanner\Scanner',
      'parser' => 'ApiGen\Parser\Parser',
      'parserResult' => 'ApiGen\Parser\ParserResult',
      'generatorQueue' => 'ApiGen\Generator\GeneratorQueue',
      'themeResources' => 'ApiGen\Theme\ThemeResources',
      'fileSystem' => 'ApiGen\FileSystem\FileSystem',
    ];
    $this->services = $services + $defaultServices;
    $baseClass = new \ReflectionClass('\ApiGen\ApiGen');
    $rootDir = dirname($baseClass->getFileName()) . '/..';
    $this->container = $this->initContainer($rootDir, $cacheDir);
    return;
  }
  
  protected function initContainer($rootDir, $cacheDir) {
    $configurator = new Configurator;
    #$configurator->setDebugMode( ! Tracy\Debugger::$productionMode);
    $configurator->setTempDirectory($cacheDir);
    #$configurator->addConfig($rootDir . '/src/DI/config.neon');
    $configurator->addConfig(__DIR__ . '/ApiGen/DI/config.neon');
    $configurator->addServices([]);
    $configurator->addParameters(['rootDir' => $rootDir]);
    return $configurator->createContainer();
  }
  
  public function getService($name) {
    if(!isset($this->services[$name])) {
      throw new \Exception("Unknown service alias \"$name\".");
    }
    return $this->container->getByType($this->services[$name]);
  }
  
}