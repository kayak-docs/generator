<?php

namespace KayakDocs\Generator;

use Nette\Configurator;

class ApiGenBridge {

  /**
   * @var Configurator
   */
  private $configurator;

  /**
   * @var \Nette\DI\Container
   */
  private $container;

  /**
   * @var array
   *   A map of service class shorthands.
   */
  private $serviceAliases = [
    'configuration'  => 'ApiGen\Configuration\Configuration',
    'generatorQueue' => 'ApiGen\Generator\GeneratorQueue',
    'parser'         => 'ApiGen\Parser\Parser',
    'parserResult'   => 'ApiGen\Parser\ParserResult',
    'scanner'        => 'ApiGen\Scanner\Scanner',
    'themeResources' => 'ApiGen\Theme\ThemeResources',
  ];

  /**
   * ApiGenBridge constructor.
   * 
   * @param string $cacheDir
   *   
   */
  public function __construct($cacheDir) {
    // Find the ApiGen src directory.
    $baseClass = new \ReflectionClass('\ApiGen\ApiGen');
    $rootDir = dirname($baseClass->getFileName()) . '/..';
    
    $this->configurator = $this->initConfigurator($rootDir, $cacheDir);
    $this->container = $this->configurator->createContainer();
  }

  /**
   * Recreates the DI container.
   */
  public function reinit() {
    $this->container = $this->configurator->createContainer();
  }

  public function getService($alias) {
    return $this->container->getByType($this->getServiceType($alias), true);
  }

  protected function initConfigurator($rootDir, $cacheDir) {
    $configurator = new Configurator;
    $configurator->setTempDirectory($cacheDir);
    $configurator->addConfig(__DIR__ . '/ApiGen/DI/config.neon');
    $configurator->addParameters(['rootDir' => $rootDir]);
    return $configurator;
  }
  
  private function getServiceType($alias) {
    if(!isset($this->serviceAliases[$alias])) {
      throw new \Exception("Unknown service alias \"$alias\".");
    }
    return $this->serviceAliases[$alias];
  }
  
}