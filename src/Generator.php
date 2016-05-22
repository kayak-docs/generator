<?php

namespace KayakDocs\Generator;

use ApiGen\Configuration\ConfigurationOptions as CO;
use ApiGen\Generator\GeneratorQueue;
use ApiGen\Parser\ParserResult;

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
    $sources = $this->packageManager->getPackageSources();

    $results = [];
    
    foreach($sources as $name => $sourceDir) {
      $this->processPackage($name, $sourceDir);
    }

    return $results;
  }
  
  protected function processPackage($name, $sourceDir) {
    $config = $this->bridge->getService('configuration');
    $scanner = $this->bridge->getService('scanner');
    $parser = $this->bridge->getService('parser');
    /** @var ParserResult $parserResult */
    $parserResult = $this->bridge->getService('parserResult');
    /** @var GeneratorQueue $queue */
    $queue = $this->bridge->getService('generatorQueue');
    $themeResources = $this->bridge->getService('themeResources');
    $fileSystem = $this->bridge->getService('fileSystem');

    $config->resolveOptions([
      CO::SOURCE => $sourceDir,
      CO::DESTINATION => Directory::prepare($this->targetDir . '/' . $name),
      CO::TEMPLATE_THEME => 'default',
    ]);
    $fileSystem->purgeDir($config->getOption(CO::DESTINATION));
    $themeResources->copyToDestination($config->getOption(CO::DESTINATION));

    $files = $scanner->scan(
      $config->getOption(CO::SOURCE),
      $config->getOption(CO::EXCLUDE),
      $config->getOption(CO::EXTENSIONS)
    );
    $parser->parse($files);
    #$results[$name] = ['classes' => (array) $parserResult->getClasses()];
    $queue->run();
  }
  
}
