<?php


require __DIR__.'/Autoload.php';

$Autoload = new \Rundiz\NestedSet\Tests\Autoload();
$Autoload->addNamespace('Rundiz\\NestedSet\\Tests', __DIR__);
$Autoload->addNamespace('Rundiz\\NestedSet', dirname(dirname(__DIR__)).'/src');
$Autoload->register();