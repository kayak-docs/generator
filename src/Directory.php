<?php

namespace KayakDocs\Generator;

use Symfony\Component\Filesystem\Filesystem;

class Directory {

  protected $fs;

  protected $path;

  public function __construct($path) {
    $this->fs = new Filesystem();
    $this->path = $path;
    $this->ensure();
  }

  public static function prepare($path) {
    $instance = new static($path);
    return (string) $instance;
  }

  protected function parentPerms() {
    $parts = explode('/', $this->path);
    while($parts && false === realpath(implode('/', $parts))) {
      array_pop($parts);
    }
    return fileperms(implode('/', $parts));
  }

  protected function ensure() {
    $this->fs->mkdir($this->path, $this->parentPerms($this->path));
  }

  public function clear() {
    $this->fs->remove($this->path);
    $this->ensure();
  }

  public function __toString() {
    return $this->path;
  }

}
