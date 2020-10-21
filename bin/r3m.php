<?php

$dir = __DIR__;

$dir_vendor =
dirname($dir, 1) .
DIRECTORY_SEPARATOR .
'vendor' .
DIRECTORY_SEPARATOR;

$autoload = $dir_vendor . 'autoload.php';
$autoload = require $autoload;

$config = new R3m\Io\Config(
    [
        'dir.vendor' => $dir_vendor
    ]
    );

$config->data('framework.environment', R3m\Io\Config::MODE_DEVELOPMENT);

$app = new R3m\Io\App($autoload, $config);
if(method_exists($app, 'beforeRun')){
	echo R3m\Io\App::beforeRun($app);
}
echo R3m\Io\App::run($app);
if(method_exists($app, 'afterRun')){
	echo R3m\Io\App::afterRun($app);
}
