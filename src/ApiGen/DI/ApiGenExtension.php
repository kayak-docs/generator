<?php

namespace KayakDocs\Generator\ApiGen\DI;

class ApiGenExtension extends \ApiGen\DI\ApiGenExtension {

  public function loadConfiguration()
  {
    $this->loadServicesFromConfig();
    $this->setupTemplating();
  }


  public function beforeCompile()
  {
    $builder = $this->getContainerBuilder();
    $builder->prepareClassList();
    $this->setupTemplatingFilters();
    $this->setupGeneratorQueue();
  }

  private function getSourceRoot() {
    $baseClass = new \ReflectionClass('\ApiGen\ApiGen');
    return dirname($baseClass->getFileName());
  }

  private function loadServicesFromConfig()
  {
    $builder = $this->getContainerBuilder();
    $config = $this->loadFromFile($this->getSourceRoot() . '/DI/apigen.services.neon');
    $this->compiler->parseServices($builder, $config);
  }


  private function setupTemplating()
  {
    $builder = $this->getContainerBuilder();
    $builder->addDefinition($this->prefix('latteFactory'))
      ->setClass('KayakDocs\Generator\Latte\Engine\JsonEngine')
      ->addSetup('setTempDirectory', [$builder->expand('%tempDir%/cache/json')]);
  }


  private function setupTemplatingFilters()
  {
    $builder = $this->getContainerBuilder();
    $latteFactory = $builder->getDefinition($builder->getByType('Latte\Engine'));
    foreach (array_keys($builder->findByTag(self::TAG_LATTE_FILTER)) as $serviceName) {
      $latteFactory->addSetup('addFilter', [NULL, ['@' . $serviceName, 'loader']]);
    }
  }


  private function setupGeneratorQueue()
  {
    $builder = $this->getContainerBuilder();
    $generator = $builder->getDefinition($builder->getByType('ApiGen\Generator\GeneratorQueue'));
    foreach (array_keys($builder->findByTag(self::TAG_TEMPLATE_GENERATOR)) as $serviceName) {
      $generator->addSetup('addToQueue', ['@' . $serviceName]);
    }
  }


}