<?php

namespace KayakDocs\Generator;

use ApiGen\Configuration\ConfigurationOptions as CO;
use ApiGen\Generator\GeneratorQueue;
use ApiGen\Parser\Parser;
use ApiGen\Parser\ParserResult;
use ApiGen\Scanner\Scanner;
use ApiGen\Theme\ThemeResources;

class Generator {


  /**
   * @var string
   */
  protected $cacheDir;

  /**
   * @var string
   */
  protected $targetDir;

  /**
   * @var string
   */
  protected $configFile = __DIR__ . '/../packages.json';

  /**
   * @var PackageManager
   */
  protected $packageManager;

  /**
   * @var ApiGenBridge
   */
  protected $bridge;

  
  public function __construct(PackageManager $packageManager, ApiGenBridge $bridge, $cacheDir, $targetDir) {
    $this->packageManager = $packageManager;
    $this->bridge = $bridge;
    $this->cacheDir = $cacheDir;
    $this->targetDir = $targetDir;
  }
  
  public static function create($cacheDir, $targetDir) {
    return new static(
      PackageManager::create(Directory::prepare($cacheDir . '/packages')),
      new ApiGenBridge(Directory::prepare($cacheDir . '/apigen-bridge')),
      $cacheDir,
      $targetDir
    );
  }
  
  public function getPackageManager() {
    return $this->packageManager;
  }
  
  public function process() {
    $this->readConfig();
    $sources = $this->packageManager->getPackageSources();

    $results = [];
    
    foreach($sources as $name => $sourceDir) {
      $this->processPackage($name, $sourceDir);
    }

    return $results;
  }
  
  protected function processPackage($name, $sourceDir) {
    $config = $this->bridge->getService('configuration');
    /** @var Scanner $scanner */
    $scanner = $this->bridge->getService('scanner');
    /** @var ParserResult $parserResult */
    $parserResult = $this->bridge->getService('parserResult');
    /** @var Parser $parser */
    $parser = $this->bridge->getService('parser');
    /** @var GeneratorQueue $queue */
    $queue = $this->bridge->getService('generatorQueue');
    /** @var ThemeResources $queue */
    $themeResources = $this->bridge->getService('themeResources');
    
    $targetDir = new Directory($this->targetDir . '/' . $name);
    $targetDir->purge();

    $config->resolveOptions([
      CO::SOURCE => $sourceDir,
      CO::DESTINATION => (string) $targetDir,
      CO::TEMPLATE_THEME => 'default',
    ]);
    $themeResources->copyToDestination($config->getOption(CO::DESTINATION));

    $files = $scanner->scan(
      $config->getOption(CO::SOURCE),
      $config->getOption(CO::EXCLUDE),
      $config->getOption(CO::EXTENSIONS)
    );
    $parser->parse($files);
    $queue->run();

    // Destroy all services instances to clear runtime caches.
    $this->bridge->reinit();
  }

  private function readConfig() {
    $file = file_get_contents($this->configFile);
    $config = json_decode($file);
    foreach ($config->repositories as $repository) {
      $this->getPackageManager()->addRepository($repository);
    }
    foreach ($config->packages as $name => $version) {
      $this->getPackageManager()->addPackage($name . ':' . $version);
    }
  }

}
