<?php

namespace R3m\Io;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Handler;
use R3m\Io\Module\Host;
use R3m\Io\Module\Autoload;
use R3m\Io\Module\Route;
use R3m\Io\Module\File;
use R3m\Io\Module\FileRequest;
use R3m\Io\Module\View;

use Exception;

class App extends Data {
    public const NAMESPACE = __NAMESPACE__;
    public const NAME = 'App';
    public const R3M = 'R3m';

    public const DATA_ROUTE = App::NAMESPACE . '.' . Route::NAME;
    public const DATA_CONFIG = App::NAMESPACE . '.' . Config::NAME;
    public const DATA_REQUEST = App::NAMESPACE . '.' . Handler::NAME_REQUEST . '.' . Handler::NAME_INPUT;
    public const DATA_AUTOLOAD_COMPOSER = App::NAMESPACE . '.' . 'Autoload' . '.' . 'Composer';

    public function __construct($autoload, $config){
        $this->data(App::DATA_AUTOLOAD_COMPOSER, $autoload);
        $this->data(App::DATA_CONFIG, $config);
        App::is_cli();
        require_once 'debug.php';
    }

    public static function run($object){
        Handler::request_configure($object);
        Host::configure($object);
        View::configure($object);
        Autoload::configure($object);
        Route::configure($object);
        $file = FileRequest::get($object);
        if($file === false){
            $route = Route::request($object);
            if($route === false){
                throw new Exception('couldn\'t determine route');
            } else {
                $result = $route->controller::{$route->function}($object);
                return $result;
            }
        } else {
            return $file;
        }
    }

    public function request($attribute=null, $value=null){
        $object = $this;
        if($attribute !== null && $value !== null){
            if($attribute == 'delete'){
                Core::object_delete($value, $object);
            } else {
                Core::object_set($attribute, $value, $object->data('R3m\\Io.Request.Input'));
            }

        }
        elseif($attribute !== null){
            return Core::object_get($attribute, $object->data('R3m\\Io.Request.Input'));
        }
    }

    public static function parameter($object, $parameter='', $offset=0){
        return parent::parameter($object->data(App::DATA_REQUEST), $parameter, $offset);
    }

    public function session($attribute=null, $value=null){
        return Handler::session($attribute, $value);
    }

    public function cookie($attribute=null, $value=null){
        return Handler::cookie($attribute, $value);
    }

    public static function is_cli(){
        if(!defined('IS_CLI')){
            return Core::is_cli();
        } else {
            return true;
        }
    }
}
