<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io;

use stdClass;
use Exception;
use R3m\Io\Module\Autoload;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Database;
use R3m\Io\Module\File;
use R3m\Io\Module\FileRequest;
use R3m\Io\Module\Handler;
use R3m\Io\Module\Host;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Route;
use R3m\Io\Module\View;

class App extends Data {
    const NAMESPACE = __NAMESPACE__;
    const NAME = 'App';
    const R3M = 'R3m';

    const SCRIPT = 'script';
    const LINK = 'link';

    const CONTENT_TYPE = 'contentType';
    const CONTENT_TYPE_CSS = 'text/css';
    const CONTENT_TYPE_HTML = 'text/html';
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_CLI = 'text/cli';
    const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    const ROUTE = App::NAMESPACE . '.' . Route::NAME;
    const CONFIG = App::NAMESPACE . '.' . Config::NAME;
    const REQUEST = App::NAMESPACE . '.' . Handler::NAME_REQUEST . '.' . Handler::NAME_INPUT;
    const DATABASE = App::NAMESPACE . '.' . Database::NAME;
    const REQUEST_HEADER = App::NAMESPACE . '.' . Handler::NAME_REQUEST . '.' . Handler::NAME_HEADER;

    const AUTOLOAD_COMPOSER = App::NAMESPACE . '.' . 'Autoload' . '.' . 'Composer';
    const AUTOLOAD_R3M = App::NAMESPACE . '.' . 'Autoload' . '.' . App::R3M;

    public function __construct($autoload, $config){
        $this->data(App::AUTOLOAD_COMPOSER, $autoload);
        $this->data(App::CONFIG, $config);
        App::is_cli();
        require_once 'Debug.php';
        require_once 'Error.php';
    }

    public static function run($object){
        Config::configure($object);
        Handler::request_configure($object);
        Host::configure($object);
        Autoload::configure($object);
        Route::configure($object);    
        $file = FileRequest::get($object);
        if($file === false){
            $route = Route::request($object);            
            if($route === false){
                throw new Exception('couldn\'t determine route');
            } else {
                App::contentType($object);                
                App::controller($object, $route);
                $methods = get_class_methods($route->controller);
                if(empty($methods)){
                    throw new Exception('couldn\'t determine controller');
                    $methods = [];
                }
                if(in_array('controller', $methods)){
                    $route->controller::controller($object);
                }
                if(in_array('configure', $methods)){
                    $route->controller::configure($object);
                }
                if(in_array('before_run', $methods)){
                    $route->controller::before_run($object);
                }
                if(in_array($route->function, $methods)){
                    $result = $route->controller::{$route->function}($object);
                } else {
                    throw new Exception('Cannot call: ' . $route->function . ' in: ' . $route->controller);
                }                
                if(in_array('after_run', $methods)){
                    $route->controller::after_run($object);
                }
                if(in_array('before_result', $methods)){
                    $route->controller::before_result($object);
                }
                $result = App::result($object, $result);
                if(in_array('after_result', $methods)){
                    $route->controller::after_result($object);
                }
                return $result;
            }
        } else {
            return $file;
        }
    }    

    public static function controller(\R3m\Io\App $object, $route){
        $check = @class_exists($route->controller);
        if(empty($check)){
            throw new Exception('Cannot call controller (' . $route->controller .')');
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
            throw new Exception('Couldn\'t determine contentType');
        }
        return $object->data(App::CONTENT_TYPE, $contentType);
    }

    private static function result($object, $output){
        $contentType = $object->data(App::CONTENT_TYPE);
        if($contentType == App::CONTENT_TYPE_JSON){
            $json = new stdClass();
            $json->html = $output;
            if($object->data('method')){
                $json->method = $object->data('method');
            } else {
                $json->method = $object->request('method');
            }
            if($object->data('target')){
                $json->target = $object->data('target');
            } else {
                $json->target = $object->request('target');
            }
            $append_to = $object->data('append-to');
            if(empty($append_to)){
                $append_to = $object->data('append.to');
            }
            if(empty($append_to)){
                $append_to = $object->request('append-to');
            }
            if(empty($append_to)){
                $append_to = $object->request('append.to');
            }
            if($append_to){
                if(empty($json->append)){
                    $json->append = new stdClass();
                }
                $json->append->to = $append_to;
            }
            $json->script = $object->data(App::SCRIPT);
            $json->link = $object->data(App::LINK);
            return Core::object($json, Core::OBJECT_JSON);
        }        
        return $output;
    }

    public function route(){
        return $this->data(App::ROUTE);
    }

    public function config($attribute=null, $value=null){
        return $this->data(App::CONFIG)->data($attribute, $value);
    }

    public function request($attribute=null, $value=null){                
        return $this->data(App::REQUEST)->data($attribute, $value);        
    }

    public static function parameter($object, $parameter='', $offset=0){
        return parent::parameter($object->data(App::REQUEST)->data(), $parameter, $offset);
    }

    public function session($attribute=null, $value=null){
        return Handler::session($attribute, $value);
    }

    public function cookie($attribute=null, $value=null){
        return Handler::cookie($attribute, $value);
    }

    public function data_read($url, $attribute=null){
        if(File::exist($url)){
            $read = File::read($url);
            if($read){
                $data = new Data(Core::object($read));
            } else {
                $data = new Data();
            }
            if($attribute !== null){
                $this->data($attribute, $data);
            }
            return $data;
        } else {
            return false;
        }
    }

    public function parse_read($url, $attribute=null){        
        if(File::exist($url)){
            $read = File::read($url);
            if($read){
                $mtime = File::mtime($url);
                $parse = new Parse($this);
                $parse->storage()->data('r3m.io.parse.view.url', $url);
                $parse->storage()->data('r3m.io.parse.view.mtime', $mtime);
                $data = clone $this->data();
                unset($data->{APP::NAMESPACE});
                $config = $this->data(App::CONFIG);
                $data->r3m = new stdClass();
                $data->r3m->io = new stdClass();
                $data->r3m->io->config = $config->data();
                $read = $parse->compile(Core::object($read), $data, $parse->storage());
                $data = new Data($read);
                Parse::readback($this, $parse, App::SCRIPT);
                Parse::readback($this, $parse, App::LINK);
            } else {
                $data = new Data();
            }
            if($attribute !== null){
                $this->data($attribute, $data);
            }
            return $data;
        } else {
            return false;
        }
    }

    public static function is_cli(){
        if(!defined('IS_CLI')){
            return Core::is_cli();
        } else {
            return true;
        }
    }
}
