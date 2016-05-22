<?php

namespace KayakDocs\Generator;

use Composer\Composer;
use Composer\Factory;
use Composer\Installer;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
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
    $io = new NullIO();
    $composer = Factory::create($io);
    return new static($io, $composer, $cacheDir);
  }

  public function addSchema($source) {
    $composer = Factory::create($this->io);

  }

  public function addPackage($packageString, $requireDepth = null) {
    list($name, $constraint) = explode(':', $packageString, 2) + [null, '*'];
    
    $installer = Installer::create($this->io, $this->composer);
    $installer->setDryRun(true);
    $installer->setPreferStable(true);
    
    $package = $this->composer->getRepositoryManager()->findPackage($name, $constraint);
    if(!$package) {
      throw new \Exception("Package \"$packageString\" not found.");
    }

    if(is_null($requireDepth) || $requireDepth > 0) {
      $recursiveDepth = !is_null($requireDepth) ? $requireDepth - 1 : null;
      foreach($package->getRequires() as $link) {
        // Assume anything without "/" is php or an extension.
        if(!strstr($link->getTarget(), '/')) {
          continue;
        }
        $linkTarget = $link->getTarget() . ':' . $link->getConstraint()->getPrettyString();
        $this->addPackage($linkTarget, $recursiveDepth);
      }
    }

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