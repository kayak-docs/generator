<?php

require __DIR__ . '/vendor/autoload.php';

ini_set('error_reporting', E_ALL);

$generator = \KayakDocs\Generator\Generator::create(__DIR__ . '/tmp', __DIR__ . '/docs');
$generator->getPackageManager()->addRepository('https://packagist.drupal-composer.org');
$generator->getPackageManager()->addPackage('guzzlehttp/guzzle:6.2.0', null);
#$generator->getPackageManager()->addPackage('drupal/paragraphs:8.1.0-rc4');
$results = $generator->process();

return;
