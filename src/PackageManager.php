<?php

namespace KayakDocs\Generator;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackageInterface;
use Composer\Repository\RepositoryFactory;

class PackageManager {

  /**
   * @var IOInterface
   */
  protected $io;
  
  /**
   * @var Composer
   */
  protected $composer;
  
  protected $cacheDir;

  /**
   * @var CompletePackageInterface[]
   */
  protected $packages = [];

  
  public function __construct(IOInterface $io, Composer $composer, $cacheDir) {
    $this->io = $io;
    $this->composer = $composer;
    $this->cacheDir = $cacheDir;
  }
  
  public static function create($cacheDir) {
    $io = new \Composer\IO\NullIO();
    $composer = Factory::create($io);
    return new static($io, $composer, $cacheDir);
  }

  public function addPackage($packageString) {
    list($name, $constraint) = explode(':', $packageString, 2) + [null, '*'];
    $package = $this->composer->getRepositoryManager()->findPackage($name, $constraint);
    $this->packages[$packageString] = $package;
  }

  public function addRepository($url) {
    $repository = RepositoryFactory::fromString($this->io, $this->composer->getConfig(), $url);
    $this->composer->getRepositoryManager()->addRepository($repository);
  }
  
  public function getPackageSources() {
    $sources = [];
    foreach($this->packages as $package) {
      $name = $package->getUniqueName();
      $packageDir = $this->cacheDir . '/sources/' . $name;
      if(!file_exists($packageDir)) {
        $this->composer->getDownloadManager()->download($package, $packageDir);
      }
      $sources[$name] = $packageDir;
    }
    return $sources;
  }
  
}