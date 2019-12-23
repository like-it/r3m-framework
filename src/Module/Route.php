<?php
/**
 *  (c) 2019 Priya.software
 *
 *  License: MIT
 *
 *  Author: Remco van der Velde
 *  Version: 1.0
 */

namespace R3m\Io\Module;

use stdClass;
use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Handler;
use R3m\Io\Module\File;
use R3m\Io\Module\Core;

class Route extends Data{
    public const NAMESPACE = __NAMESPACE__;
    public const NAME = 'Route';
    public const SELECT = 'Route_select';

    private const SELECT_DEFAULT = 'info';

    private $current;

    public function current($current=null){
        if($current !== null){
            $this->setCurrent($current);
        }
        return $this->getCurrent();
    }

    private function setCurrent($current=null){
        $this->current = $current;
    }

    private function getCurrent(){
        return $this->current;
    }

    public static function get($object, $name='', $option=[]){
        $route = $object->data(App::DATA_ROUTE);
        $get = $route->data($name);
        if(empty($get)){
            return;
        }
        foreach($option as $key => $value){
            if(is_numeric($key)){
                $explode = explode('}', $get->path, 2);
                $temp = explode('{$', $explode[0], 2);

                if(array_key_exists(1, $temp)){
                    $key = $temp[1];
                    $get->path = str_replace('{$' . $key . '}', $value, $get->path);
                }
            } else {
                $get->path = str_replace('{$' . $key . '}', $value, $get->path);
            }

        }
        $url = $object->data('host.url') . $get->path;
        return $url;
    }

    private static function input_request($object, $input, $glue='/'){
        $request = [];
        foreach($input as $key => $value){
            $request[] = $value;
        }
        $input->request = implode($glue, $request);
        if(substr($input->request, -1, 1) != $glue){
            $input->request .= $glue;
        }
        return $input;
    }

    private static function add_request($object, $request){
        if(empty($request)){
            return $object;
        }
        $temp =  $object->data(App::DATA_REQUEST);

//         d($request);
        if(!property_exists($request, 'request')){
            return $object;
        }
        if(is_array($request->request) || is_object($request->request)){
            foreach($request->request as $key => $value){
                $temp->{$key} = $value;
            }
        }
        return $object;
    }

    private static function select_info($object, $record){
        $select = new stdClass();
        $select->parameter = new stdClass();
        $select->attribute = [];
        $select->method = Handler::method();
        $select->host = [];
        $select->attribute[] = Route::SELECT_DEFAULT;
        $key = 0;
        $select->parameter->{$key} =  Route::SELECT_DEFAULT;
        foreach($record->parameter as $key => $value){
            $select->parameter->{$key + 1} = $value;
        }
        return $select;
    }

    public static function request($object){
        if(defined('IS_CLI')){
            $input = Route::input($object);
            $select = new stdClass();
            $select->parameter = $input;
            $key = 0;
            $select->attribute = [];
            if(property_exists($select->parameter, $key)){
                $select->attribute[] = $select->parameter->{$key};
            } else {
                $select->attribute[] = '';
            }
            $select->method = Handler::method();
            $select->host = [];
            $request = Route::select_cli($object, $select);
            if($request === false){
                $select = Route::select_info($object, $select);
                $request = Route::select_cli($object, $select);
//                 throw new Exception('Request not found');
            }
            if(property_exists($request, 'request') && is_object($request->request)){
                $request->request = Core::object_merge(clone $select->parameter, $request->request);
            } else {
                $request->request = $select->parameter;
            }
            $route =  $object->data(App::DATA_ROUTE);
            $object = Route::add_request($object, $request);
            return $route->current($request);
        } else {
            $input = Route::input($object);
            $select = new stdClass();
            $select->input = $input;
            $select->deep = substr_count($input->request, '/');
            $select->attribute = explode('/', $input->request);
            array_pop($select->attribute);
            $select->method = Handler::method();
            $select->host = [];

            $subdomain = Host::subdomain();
            if($subdomain){
                $select->host[] = $subdomain . '.' . Host::domain() . '.' . Host::extension();
//                 $select->host[] = $subdomain . '.' . Host::domain() . '.' . 'local';
//                 $select->host[] = $subdomain . '.' . Host::domain() . '.' . 'develop';
            } else {
                $select->host[] = Host::domain() . '.' . Host::extension();
//                 $select->host[] = Host::domain() . '.' . 'local';
//                 $select->host[] = Host::domain() . '.' . 'develop';
            }
            $select->host = array_unique($select->host);
            $request = Route::select($object, $select);

            $route =  $object->data(App::DATA_ROUTE);
//             dd($route);
            return $route->current($request);
        }
    }

    public static function input($object){
        $input = $object->data(App::DATA_REQUEST);
        return $input;
    }

    private static function select_cli($object, $select){
        $route =  $object->data(App::DATA_ROUTE);
        $match = false;
        $data = $route->data();
        if(Core::object_is_empty($data)){
            return false;
        }
        if(!is_object($data)){
            return false;
        }
        $current = false;
        foreach($data as $record){
            if(property_exists($record, 'resource')){
                continue;
            }
            $match = Route::is_match_cli($object, $record, $select);
            //             d($record);
            //             d($select);
            if($match === true){
                $current = $record;
                break;
            }
        }
        if($current !== false){
            $current = Route::prepare($object, $current, $select);
            $current->parameter = $select->parameter;
            //             $current->select = $select;
            return $current;
        }
        return false;
    }


    private static function select($object, $select){
        $route =  $object->data(App::DATA_ROUTE);
        $match = false;
        $data = $route->data();
        if(empty($data)){
            return $select;
        }
        if(!is_object($data)){
            return $select;
        }
        $current = false;
        foreach($data as $record){
            if(property_exists($record, 'resource')){
                continue;
            }
            if(!property_exists($record, 'deep')){
                continue;
            }
            $match = Route::is_match($object, $record, $select);
//             d($record);
//             d($select);
            if($match === true){
                $current = $record;
                break;
            }
        }
        if($current !== false){
            $current = Route::prepare($object, $current, $select);
//             $current->select = $select;
            return $current;
        }
        return false;
    }

    private static function add_localhost($object, $route){
        if(!property_exists($route, 'host')){
            return $route;
        }
        $allowed_host = [];
        $disallowed_host = [];
        foreach($route->host as $host){
            if(substr($host, 0, 1) == '!'){
                $disallowed_host[] = $host;
                continue;
            }
            $allowed_host[] = $host;
        }

        $config =  $object->data(App::DATA_CONFIG);
        $localdomain = $config->data(Config::LOCALHOST_EXTENSION);

        $allowed_host_new = [];
        $disallowed_host_new = [];

        if(is_array($localdomain)){
            foreach($allowed_host as $host){
                $allowed_host_new[] = $host;
                $explode = explode('.', $host);
                array_pop($explode);
                $prefix = implode('.', $explode);
                foreach($localdomain as $extension){
                    $allowed_host_new[] = $prefix . '.' . $extension;
                }
            }
            foreach($disallowed_host as $host){
                $disallowed_host_new[] = $host;
                $explode = explode('.', $host);
                array_pop($explode);
                $prefix = implode('.', $explode);
                foreach($localdomain as $extension){
                    $disallowed_host_new[] = $prefix . '.' . $extension;
                }
            }
            $route->host = array_merge($allowed_host_new, $disallowed_host_new);
        }
        return $route;
    }

    private static function is_variable($string){
        $string = trim($string);
        if(
            substr($string, 0, 2) == '{$' &&
            substr($string, -1) == '}'
        ){
            return true;
        }
        return false;
    }

    private static function get_variable($string){
        $string = trim($string);
        if(
            substr($string, 0, 2) == '{$' &&
            substr($string, -1) == '}'
        ){
            return substr($string, 2, -1);
        }
    }

    private static function prepare($object, $route, $select){
        $explode = explode('/', $route->path);
        array_pop($explode);
        $attribute = $select->attribute;
        if(!property_exists($route, 'request')){
            $route->request = new stdClass();
        }
        foreach($explode as $nr => $part){
            if(Route::is_variable($part)){
                $variable = Route::get_variable($part);
//                 d($variable);
                if(property_exists($route->request, $variable)){
                    continue;
                }
//                 d($attribute);
//                 d($variable);
                if(array_key_exists($nr, $attribute)){
                    $route->request->{$variable} = $attribute[$nr];
                }
//                 d($route->request);
            }
        }
        $controller = explode('.', $route->controller);
        $function = array_pop($controller);
        $route->controller = implode('\\', $controller);
        $route->function = $function;
        return $route;
    }

    private static function is_match_by_attribute($object, $route, $select){
        $explode = explode('/', $route->path);
        array_pop($explode);
        $attribute = $select->attribute;
        if(empty($attribute)){
            return true;
        }
        foreach($explode as $nr => $part){
            if(Route::is_variable($part)){
                continue;
            }
            if(array_key_exists($nr, $attribute) === false){
                return false;
            }
            if($part != $attribute[$nr]){
                return false;
            }
        }
        return true;
    }

    private static function is_match_by_method($object, $route, $select){
        if(!property_exists($route, 'method')){
            return false;
        }
        if(!is_array($route->method)){
            return false;
        }
        foreach($route->method as $method){
            if(strtoupper($method) == strtoupper($select->method)){
                return true;
            }
        }
        return false;
    }

    private static function is_match_by_host($object, $route, $select){
        if(!property_exists($route, 'host')){
            return true;
        }
        if(!is_array($route->host)){
            return false;
        }
        $allowed_host = [];
        $disallowed_host = [];
        foreach($select->host as $host){
            $host = strtolower($host);
            if(substr($host, 0, 1) == '!'){
                $disallowed_host[] = substr($host, 1);
                continue;
            }
            $allowed_host[] = $host;
        }
        foreach($route->host as $host){
            if(in_array($host, $disallowed_host)){
                return false;
            }
            if(in_array($host, $allowed_host)){
                return true;
            }
        }
        return false;
    }

    private function is_match_by_deep($object, $route, $select){
        if(!property_exists($route, 'deep')){
            return false;
        }
        if(!property_exists($select, 'deep')){
            return false;
        }
        if($route->deep != $select->deep){
            return false;
        }
        return true;
    }

    private static function is_match_cli($object, $route, $select){
        /*
        $is_match = Route::is_match_by_deep($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $route = Route::add_localhost($object, $route);
        $is_match = Route::is_match_by_host($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        */
        $is_match = Route::is_match_by_attribute($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $is_match = Route::is_match_by_method($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        return $is_match;
    }

    private static function is_match($object, $route, $select){
        $is_match = Route::is_match_by_deep($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $route = Route::add_localhost($object, $route);
        $is_match = Route::is_match_by_host($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $is_match = Route::is_match_by_attribute($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $is_match = Route::is_match_by_method($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        return $is_match;
    }

    public static function configure($object){
        $config = $object->data(App::DATA_CONFIG);
        $url = $config->data(Config::DATA_PROJECT_DIR_DATA) . $config->data(Config::DATA_PROJECT_ROUTE_FILENAME);

        if(empty($config->data(Config::DATA_PROJECT_ROUTE_URL))){
            $config->data(Config::DATA_PROJECT_ROUTE_URL, $url);
        }
        $url = $config->data(Config::DATA_PROJECT_ROUTE_URL);

        if(File::Exist($url)){
            $read = File::read($url);
            $data = new Route(Core::object($read));
            $object->data(App::DATA_ROUTE, $data);
        }
        Route::load($object);
    }

    private function item_path($object, $item){
        if(!property_exists($item, 'path')){
            return $item;
        }
        if(substr($item->path, 0, 1) == '/'){
            $item->path = substr($item->path, 1);
        }
        if(substr($item->path, -1) !== '/'){
            $item->path .= '/';
        }
        return $item;

    }

    private function item_deep($object, $item){
        $item->deep = substr_count($item->path, '/');
        return $item;
    }

    public static function load($object){
        $reload = false;
        $route = $object->data(App::DATA_ROUTE);
        if(empty($route)){
            return;
        }
        $data = $route->data();
        if(empty($data)){
            return;
        }
        foreach($data as
            $item){
            if(!is_object($item)){
                continue;
            }
            if(!property_exists($item, 'resource')){
                $item = Route::item_path($object, $item);
                $item = Route::item_deep($object, $item);
                continue;
            }
            if(property_exists($item, 'read')){
                continue;
            }
            $item->resource = Route::parse($object, $item->resource);
            if(File::exist($item->resource)){
                $read = File::read($item->resource);
                $resource = Core::object($read);
                foreach($resource as $resource_key => $resource_item){
                    $check = $route->data($resource_key);
                    if(empty($check)){
                        $route->data($resource_key, $resource_item);
                    }
                }
                $reload = true;
                $item->read = true;
            } else {
                $item->read = false;
            }
        }
        if($reload === true){
            Route::load($object);
        }
    }

    public static function parse($object, $resource){
        $explode = explode('}', $resource, 2);
        if(!isset($explode[1])){
            return $resource;
        }
        $temp = explode('{', $explode[0], 2);
        if(isset($temp[1])){
            $attribute = substr($temp[1], 1);
            $config = $object->data(App::DATA_CONFIG);
            $value = $config->data($attribute);
            $resource = str_replace('{$' . $attribute . '}', $value, $resource);
            return Route::parse($object, $resource);
        } else {
            return $resource;
        }
    }

}