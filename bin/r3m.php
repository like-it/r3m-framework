<?php

$dir = __DIR__;

$dir_vendor = 
    dirname($dir) . 
    DIRECTORY_SEPARATOR . 
    'vendor' .
    DIRECTORY_SEPARATOR;

$autoload = $dir_vendor . 'autoload.php';
$autoload = require $autoload;

$config = new R3m\Io\Config();
$app = new R3m\Io\App($autoload, $config);

