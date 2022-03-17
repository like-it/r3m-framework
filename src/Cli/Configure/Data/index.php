<?php
/**
 * @author          Remco van der Velde
 * @since           2020-10-27
 * @version         1.0
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
$app = new R3m\Io\App($autoload, $config);
try {
    echo R3m\Io\App::run($app);
} catch (
    \R3m\Io\Exception\LocateException  |
    \R3m\Io\Exception\ObjectException |
    Exception
    $exception
) {
    echo R3m\Io\App\exception_to_json($exception);
}
