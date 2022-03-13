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

    public static function alert($message=null, $context=[]){
        $object = Logger::app();
        $object->logger()->alert($message, $context);
    }

    public static function critical($message=null, $context=[]){
        $object = Logger::app();
        $object->logger()->critical($message, $context);
    }

    public static function debug($message=null, $context=[]){
        $object = Logger::app();
        $object->logger()->debug($message, $context);
    }

    public static function emergency($message=null, $context=[]){
        $object = Logger::app();
        $object->logger()->emergency($message, $context);
    }

    public static function error($message=null, $context=[]){
        $object = Logger::app();
        $object->logger()->error($message, $context);
    }

    public static function info($message=null, $context=[]){
        $object = Logger::app();
        $object->logger()->info($message, $context);
    }

    public static function notice($message=null, $context=[]){
        $object = Logger::app();
        $object->logger()->notice($message, $context);
    }

    public static function warning($message=null, $context=[]){
        $object = Logger::app();
        $object->logger()->warning($message, $context);
    }

    public static function app(): App
    {
        $dir = __DIR__;
        $dir_vendor =
            dirname($dir, 5) .
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
