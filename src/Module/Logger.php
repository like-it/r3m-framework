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

use Exception;

class Logger {

    /**
     * @throws Exception
     */
    public static function alert($message=null, $context=[], $name=''){
        $object = App::instance();
        if(empty($name)){
            $name = $object->config('logger.default.name');
        }
        $object->logger($name)->alert($message, $context);
    }

    /**
     * @throws Exception
     */
    public static function critical($message=null, $context=[], $name=''){
        $object = App::instance();
        if(empty($name)){
            $name = $object->config('logger.default.name');
        }
        $object->logger($name)->critical($message, $context);
    }

    /**
     * @throws Exception
     */
    public static function debug($message=null, $context=[], $name=''){
        $object = App::instance();
        if(empty($name)){
            $name = $object->config('logger.default.name');
        }
        $object->logger($name)->debug($message, $context);
    }

    /**
     * @throws Exception
     */
    public static function emergency($message=null, $context=[], $name=''){
        $object = App::instance();
        if(empty($name)){
            $name = $object->config('logger.default.name');
        }
        $object->logger($name)->emergency($message, $context);
    }

    /**
     * @throws Exception
     */
    public static function error($message=null, $context=[], $name=''){
        $object = App::instance();
        if(empty($name)){
            $name = $object->config('logger.default.name');
        }
        $object->logger($name)->error($message, $context);
    }

    /**
     * @throws Exception
     */
    public static function info($message=null, $context=[], $name=''){
        $object = App::instance();
        if(empty($name)){
            $name = $object->config('logger.default.name');
        }
        $object->logger($name)->info($message, $context);
    }

    /**
     * @throws Exception
     */
    public static function notice($message=null, $context=[], $name=''){
        $object = App::instance();
        if(empty($name)){
            $name = $object->config('logger.default.name');
        }
        $object->logger($name)->notice($message, $context);
    }

    /**
     * @throws Exception
     */
    public static function warning($message=null, $context=[], $name=''){
        $object = App::instance();
        if(empty($name)){
            $name = $object->config('logger.default.name');
        }
        $object->logger($name)->warning($message, $context);
    }
}
