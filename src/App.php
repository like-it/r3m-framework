<?php

namespace R3m\Io;

use stdClass;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Database;
use R3m\Io\Module\Handler;
use R3m\Io\Module\Host;
use R3m\Io\Module\Autoload;
use R3m\Io\Module\Route;
use R3m\Io\Module\File;
use R3m\Io\Module\FileRequest;
use R3m\Io\Module\View;

use Exception;

class App extends Data {
    const NAMESPACE = __NAMESPACE__;
    const NAME = 'App';
    const R3M = 'R3m';

    const TITLE = 'meta.title';
    const SCRIPT = 'script';
    const LINK = 'link';

    const CONTENT_TYPE = 'contentType';
    const CONTENT_TYPE_CSS = 'text/css';
    const CONTENT_TYPE_HTML = 'text/html';
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_CLI = 'text/cli';
    const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    const DATA_ROUTE = App::NAMESPACE . '.' . Route::NAME;
    const DATA_CONFIG = App::NAMESPACE . '.' . Config::NAME;
    const DATABASE = App::NAMESPACE . '.' . Database::NAME;
    const DATA_REQUEST = App::NAMESPACE . '.' . Handler::NAME_REQUEST . '.' . Handler::NAME_INPUT;
    const REQUEST_HEADER = App::NAMESPACE . '.' . Handler::NAME_REQUEST . '.' . Handler::NAME_HEADER;
    const DATA_AUTOLOAD_COMPOSER = App::NAMESPACE . '.' . 'Autoload' . '.' . 'Composer';
    const DATA_AUTOLOAD_R3M = App::NAMESPACE . '.' . 'Autoload' . '.' . App::R3M;

    public function __construct($autoload, $config){
        $this->data(App::DATA_AUTOLOAD_COMPOSER, $autoload);
        $this->data(App::DATA_CONFIG, $config);
        App::is_cli();
        require_once 'debug.php';
    }

    public static function run($object){
        Config::configure($object);
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
                App::contentType($object);
                $result = $route->controller::{$route->function}($object);
                $result = App::result($object, $result);
                return $result;
            }
        } else {
            return $file;
        }
    }

    public static function contentType($object){
        $contentType = App::CONTENT_TYPE_HTML;
        if(property_exists($object->data(App::REQUEST_HEADER), '_')){
            $contentType = App::CONTENT_TYPE_CLI;
        }
        elseif(property_exists($object->data(App::REQUEST_HEADER), 'Content-Type')){
            $contentType = $object->data(App::REQUEST_HEADER)->{'Content-Type'};
        }
        if(empty($contentType)){
            d($_SERVER);
            throw new Exception('Couldn\'t determine contentType');
        }
        return $object->data(App::CONTENT_TYPE, $contentType);
    }

    private static function result($object, $output){
        $contentType = $object->data(App::CONTENT_TYPE);
        if($contentType == App::CONTENT_TYPE_JSON){
            $json = new stdClass();
            $json->html = $output;
            $json->title = $object->data(App::TITLE);
            if($object->data('method')){
                $json->method = $object->data('method');
            } else {
                $json->method = $object->data(App::DATA_REQUEST)->data('method');
            }
            if($object->data('target')){
                $json->target = $object->data('target');
            } else {
                $json->target = $object->data(App::DATA_REQUEST)->data('target');
            }
            $json->script = $object->data(App::SCRIPT);
            $json->link = $object->data(App::LINK);
            return Core::object($json, Core::OBJECT_JSON);
        }
        return $output;
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
        return parent::parameter($object->data(App::DATA_REQUEST)->data(), $parameter, $offset);
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
