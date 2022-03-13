<?php
/**
 * @author          Remco van der Velde
 * @since           13-03-2022
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use R3m\Io\App;
use R3m\Io\Config;

class Logger {

    public static function warning($message=null, $context=[]){
        $object = Logger::app();
        $object->logger()->warning($message, $context);
    }

    public static function app(): App
    {
        $dir = __DIR__;
        $dir_vendor =
            dirname($dir, 3) .
            DIRECTORY_SEPARATOR .
            'vendor' .
            DIRECTORY_SEPARATOR;

        $autoload = $dir_vendor . 'autoload.php';
        $autoload = require $autoload;
        $config = new Config(
            [
                'dir.vendor' => $dir_vendor
            ]
        );
        return new App($autoload, $config);
    }

}
