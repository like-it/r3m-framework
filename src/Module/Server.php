<?php
/**
 * @author          Remco van der Velde
 * @since           18-12-2020
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use stdClass;
use Exception;
use R3m\Io\App;
use R3m\Io\Config;

class Server {

    public static function url(App $object, $name=''){
        $name = str_replace('.', '-', $name);
        return $object->config('server.url.' . $name . '.' . $object->config('framework.environment'));
    }

    public static function token(){
        if(array_key_exists('HTTP_AUTHORIZATION', $_SERVER)){
            $explode = explode('Bearer ', $_SERVER['HTTP_AUTHORIZATION'], 2);
            if(array_key_exists(1, $explode)){
                return $explode[1];
            }
        }
    }
}
