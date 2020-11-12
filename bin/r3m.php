<?php
/**
 * @author          Remco van der Velde
 * @since           2019-10-27
 * @version         1.0
 * @license         MIT
 * @changeLog
 *     -    all
 */

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
// $config->data('framework.environment', R3m\Io\Config::MODE_DEVELOPMENT);
$app = new R3m\Io\App($autoload, $config);
echo R3m\Io\App::run($app);
