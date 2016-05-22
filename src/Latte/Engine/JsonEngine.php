<?php

namespace KayakDocs\Generator\Latte\Engine;

use Latte\Engine;

class JsonEngine extends Engine {

  public function render($name, array $params = []) {
    return parent::render($name, $params);
  }

//  public function compile($name) {
//    return '';
//  }

  public function invokeFilter($name, array $args) {
    return parent::invokeFilter($name, $args);
  }
  
}
