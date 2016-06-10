<?php

require __DIR__ . '/vendor/autoload.php';

ini_set('error_reporting', E_ALL);

$generator = \KayakDocs\Generator\Generator::create(__DIR__ . '/tmp', __DIR__ . '/docs');
$results = $generator->process();

return;
