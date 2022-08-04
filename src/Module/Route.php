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
namespace R3m\Io\Module;

use stdClass;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Handler;
use R3m\Io\Module\File;
use R3m\Io\Module\Core;

use Exception;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\UrlEmptyException;

class Route extends Data{
    const NAMESPACE = __NAMESPACE__;
    const NAME = 'Route';
    const SELECT = 'Route_select';
    const SELECT_DEFAULT = 'info';

    private $current;
    private $url;
    private $cache_url;

    public function url($url=null){
        if($url !== null){
            $this->url = $url;
        }
        return $this->url;
    }

    public function cache_url($url=null){
        if($url !== null){
            $this->cache_url = $url;
        }
        return $this->cache_url;
    }

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

    public static function has_host($select='', $url=''){
        $url = Host::remove_scheme($url);
        $allowed_host = [];
        $disallowed_host = [];
        if(property_exists($select, 'host')){
            foreach($select->host as $host){
                $host = strtolower($host);
                if(substr($host, 0, 1) == '!'){
                    $disallowed_host[] = substr($host, 1);
                    continue;
                }
                $allowed_host[] = $host;
            }
            if(in_array($url, $disallowed_host)){
                return false;
            }
            if(in_array($url, $allowed_host)){
                return $select;
            }
            return false;
        }
    }

    public static function find($object, $name='', $option=[]){
        if($name === null){
            return;
        }
        $route = $object->data(App::ROUTE);
        $get = $route->data($name);
        if(empty($get)){
            return;
        }
        if(!property_exists($get, 'path')){
            if(property_exists($get, 'url')){
                $url = $get->url;
                return $url;
            } else {
                throw new Exception('path & url are empty');
            }
        }
        $get = $route::add_localhost($object, $get);
        if(!empty($object->data('host.url'))  && property_exists($get, 'host')){
            $host = explode(':', $object->data('host.url'), 3);
            if(array_key_exists(2, $host)){
                array_pop($host);
            }
            $host = implode(':', $host);
            $get = $route::has_host($get, $host);
        }
        if(empty($get)){
            return;
        }
        $get->path = str_replace([
            '{{',
            '}}',
        ], [
            '{',
            '}'
        ], $get->path);
        $path = $get->path;
        if(is_array($option)){
            if(
                empty($option) &&
                stristr($path, '{$') !== false
            ){
                throw new Exception('path has variable & option is empty');
            }
            $old_path = $get->path;
            foreach($option as $key => $value){
                if(is_numeric($key)){
                    $explode = explode('}', $get->path, 2);
                    $temp = explode('{$', $explode[0], 2);
                    if(array_key_exists(1, $temp)){
                        $variable = $temp[1];
                        $path = str_replace('{$' . $variable . '}', $value, $path);
                        $get->path = str_replace('{$' . $variable . '}', '', $get->path);
                    }
                } else {
                    $path = str_replace('{$' . $key . '}', $value, $path);
                    $get->path = str_replace('{$' . $key . '}', '', $get->path);
                }
            }
            $get->path = $old_path;
        }
        if($path == '/'){
            $url = $object->data('host.url');
        } else {
            $url = $object->data('host.url') . $path;
        }
        $object->logger()->debug('route:find.url:', [$url]);
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
        $object->data(App::REQUEST)->data(
            Core::object_merge(
                $object->data(App::REQUEST)->data(),
                $request->request->data()
            )
        );
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

    public static function wildcard(App $object){
        if(defined('IS_CLI')){

        } else {
            $select = new stdClass();
            $select->method = Handler::method();
            $select->host = [];
            $subdomain = Host::subdomain();
            if($subdomain){
                $select->host[] = $subdomain . '.' . Host::domain() . '.' . Host::extension();
            } else {
                $domain = Host::domain();
                if($domain){
                    $select->host[] = Host::domain() . '.' . Host::extension();
                } else {
                    $select->host[] = 'localhost';
                }
            }
            $select->host = array_unique($select->host);
            $request = Route::selectWildcard($object, $select);
            $route =  $object->data(App::ROUTE);
            return $route->current($request);
        }
    }

    private static function find_array($string=''){
        $split = str_split($string);
        $is_array = false;
        $is_quote_double = false;
        $previous_char = false;
        $collection = '';
        $array = [];
        foreach($split as $nr => $char){
            if(
                $previous_char === '/' &&
                $char === '[' &&
                $is_quote_double === false
            ){
                $is_array = true;
            }
            elseif(
                $char === ']' &&
                $is_quote_double === false
            ){
                if($is_array){
                    $array[] = $collection;
                    $collection = '';
                }
                $is_array = false;
            }
            elseif(
                $char === '"' &&
                $previous_char !== '\\'
            ){
                $is_quote_double = !$is_quote_double;
            }
            if($is_array){
                $collection .= $char;
            }
            $previous_char = $char;
        }
        return $array;
    }

    private static function request_explode($input=''): array
    {
        d($input);
        $split = str_split($input);
        $is_quote_double = false;
        $in_type = false;
        $collection = '';
        $explode = [];
        $previous_char = false;
        foreach($split as $nr => $char){
            if(
                $previous_char === '/' &&
                $char === '{' &&
                $is_quote_double === false
            ){
                $is_type = true;
                if(!empty($collection)){
                    $explode[] = substr($collection, 0,-1);
                }
                $collection = $char;
                continue;
            }
            elseif(
                $previous_char === '/' &&
                $char == '[' &&
                $is_quote_double === false
            ){
                $is_type = true;
                if(!empty($collection)){
                    $explode[] = substr($collection, 0,-1);
                }
                $collection = $char;
                continue;
            }
            elseif(
                $char === '"' &&
                $previous_char !== '\\'
            ){
                $is_quote_double = !$is_quote_double;
            }
            $collection .= $char;
            $previous_char = $char;
        }
        if(!empty($collection)){
            if($previous_char === '/'){
                $explode[] = substr($collection, 0,-1);
            } else {
                $explode[] = $collection;
            }
        }
        return $explode;
    }

    /**
     * @throws UrlEmptyException
     * @throws ObjectException
     * @throws Exception
     */
    public static function request(App $object){
        if(defined('IS_CLI')){
            $input = Route::input($object);
            $select = new stdClass();
            $select->parameter = $input->data();
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
            }
            if($request === false){
                throw new Exception('Exception in request');
            }
            $request->request->data(Core::object_merge(clone $select->parameter, $request->request->data()));
            $route =  $object->data(App::ROUTE);
            $object = Route::add_request($object, $request);
            return $route->current($request);
        } else {
            if(
                Host::scheme() === Host::SCHEME_HTTP &&
                $object->config('server.http.upgrade_insecure') === true &&
                $object->config('framework.environment') !== Config::MODE_DEVELOPMENT &&
                Host::isIp4Address() === false
            ){
                $url = false;
                $subdomain = Host::subdomain();
                if($subdomain){
                    $url = Host::SCHEME_HTTPS . '://' . $subdomain . '.' . Host::domain() . '.' . Host::extension();
                } else {
                    $domain = Host::domain();
                    if ($domain) {
                        $url = Host::SCHEME_HTTPS . '://' . Host::domain() . '.' . Host::extension();
                    }
                }
                if($url) {
                    Core::redirect($url);
                }
            }
            $input = Route::input($object);
            $is_added = false;
            if(substr($input->data('request'), -1) != '/'){
                $input->data('request', $input->data('request') . '/');
                $is_added = true;
            }
            $select = new stdClass();
            $select->input = $input;
            $test = Route::request_explode(urldecode($input->data('request')));
            $test_count = count($test);
            if($test_count > 1){
                $string_count = $test[0];
                $select->deep = substr_count($string_count, '/');
                $select->attribute = explode('/', $test[0]);
                if(end($select->attribute) === ''){
                    array_pop($select->attribute);
                }
                $array = [];
                for($i=1; $i < $test_count; $i++){
                    $array[] = $test[$i];
                }
                $select->attribute = array_merge($select->attribute, $array);
                $select->deep = count($select->attribute);
            } else {
                $string_count = $input->data('request');
                $select->deep = substr_count($string_count, '/');
                $select->attribute = explode('/', $input->data('request'));
            }
            if(end($select->attribute) === ''){
                array_pop($select->attribute);
            }
            $select->method = Handler::method();
            $select->host = [];
            $subdomain = Host::subdomain();
            if($subdomain){
                $select->host[] = $subdomain . '.' . Host::domain() . '.' . Host::extension();
            } else {
                $domain = Host::domain();
                if($domain){
                    $select->host[] = Host::domain() . '.' . Host::extension();
                } else {
                    $select->host[] = 'localhost';
                }
            }
            $select->host = array_unique($select->host);
            $request = Route::select($object, $select);
            $route =  $object->data(App::ROUTE);
            Route::add_request($object, $request);
            return $route->current($request);
        }
    }

    public static function input($object){
        $input = $object->data(App::REQUEST);
        return $input;
    }

    private static function select_cli($object, $select){
        $route =  $object->data(App::ROUTE);
        if(empty($route)){
            return false;
        }
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
            if($match === true){
                $current = $record;
                break;
            }
        }
        if($current !== false){
            $current = Route::prepare($object, $current, $select);
            $current->parameter = $select->parameter;
            return $current;
        }
        return false;
    }

    private static function selectWildcard($object, $select){
        $route =  $object->data(App::ROUTE);
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
            $match = Route::is_match_by_wildcard($object, $record, $select);
            if($match === true){
                $current = $record;
                break;
            }
        }
        if($match === false){
            foreach($data as $record){
                if(property_exists($record, 'resource')){
                    continue;
                }
                if(!property_exists($record, 'deep')){
                    continue;
                }
                $match = Route::is_match_by_wildcard_has_slash_in_attribute($object, $record, $select);
                if($match === true){
                    $current = $record;
                    break;
                }
            }
        }
        if($current !== false){
            if(property_exists($current, 'controller')){
                $current = Route::controller($current);
            }
            return $current;
        }
        return false;
    }

    private static function select($object, $select){
        $route =  $object->data(App::ROUTE);
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
            if($match === true){
                $current = $record;
                break;
            }
        }
        if($match === false){
            foreach($data as $record){
                if(property_exists($record, 'resource')){
                    continue;
                }
                if(!property_exists($record, 'deep')){
                    continue;
                }
                $match = Route::is_match_has_slash_in_attribute($object, $record, $select);
                if($match === true){
                    $current = $record;
                    break;
                }
            }
        }
        if($current !== false){
            $current = Route::prepare($object, $current, $select);
            return $current;
        }
        return false;
    }

    public static function add_localhost($object, $route){
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
        $config =  $object->data(App::CONFIG);
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
            $host = array_merge($allowed_host_new, $disallowed_host_new);
            $route->host = array_unique($host, SORT_STRING);
        }
        return $route;
    }

    private static function is_variable($string){
        $string = trim($string);
        $string = str_replace([
            '{{',
            '}}'
        ], [
            '{',
            '}'
        ], $string);
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
        $string = str_replace([
            '{{',
            '}}'
        ], [
            '{',
            '}'
        ], $string);
        if(
            substr($string, 0, 2) == '{$' &&
            substr($string, -1) == '}'
        ){
            return substr($string, 2, -1);
        }
    }

    private static function prepare($object, $route, $select){
        $route->path = str_replace([
            '{{',
            '}}'
        ], [
            '{',
            '}'
        ], $route->path);
        $explode = explode('/', $route->path);
        array_pop($explode);
        $attribute = $select->attribute;
        $nr = 0;
        if(property_exists($route, 'request')){
            $route->request = new Data($route->request);
        } else {
            $route->request = new Data();
        }
        foreach($explode as $nr => $part){
            if(Route::is_variable($part)){
                $get_attribute = Route::get_variable($part);
                $temp = explode(':', $get_attribute, 2);
                if(array_key_exists(1, $temp)){
                    $variable = $temp[0];
                    if(property_exists($route->request, $variable)){
                        continue;
                    }
                    if(array_key_exists($nr, $attribute)){
                        $type = ucfirst($temp[1]);
                        $className = '\\R3m\\Io\\Module\\Route\\Type' . $type;
                        $exist = class_exists($className);
                        if(
                            $exist &&
                            in_array('cast', get_class_methods($className))
                        ){
                            $value = $className::cast($object, urldecode($attribute[$nr]));
                        } else {
                            $value = urldecode($attribute[$nr]);
                        }
                        $route->request->data($variable, $value);
                    }
                } else {
                    $variable = $temp[0];
                    if(property_exists($route->request, $variable)){
                        continue;
                    }
                    if(array_key_exists($nr, $attribute)){
                        $value = urldecode($attribute[$nr]);
                        $route->request->data($variable, $value);
                    }
                }
            }
        }
        if(
            !empty($variable) &&
            count($attribute) > count($explode)
        ){
            $request = '';
            for($i = $nr; $i < count($attribute); $i++){
                $request .= $attribute[$i] . '/';
            }
            $request = substr($request, 0, -1);
            $request = urldecode($request);
            $route->request->data($variable, $request);
        }
        foreach($object->data(App::REQUEST) as $key => $record){
            if($key == 'request'){
                continue;
            }
            $route->request->data($key, $record);
        }
        if(property_exists($route, 'controller')){
            $route = Route::controller($route);
        }
        return $route;
    }

    public static function controller($route){
        if(property_exists($route, 'controller')){
            $controller = explode('.', $route->controller);
            if(array_key_exists(1, $controller)) {
                $function = array_pop($controller);
                $route->controller = implode('\\', $controller);
                $route->function = $function;
            }
        }
        return $route;
    }

    private static function is_match_by_attribute($object, $route, $select){
        if(!property_exists($route, 'path')){
            return false;
        }
        $explode = explode('/', $route->path);
        array_pop($explode);
        $attribute = $select->attribute;
        if(empty($attribute)){
            return true;
        }
        $path_attribute = [];
        foreach($explode as $nr => $part){
            if(Route::is_variable($part)){
                $variable = Route::get_variable($part);
                if($variable){
                    $temp = explode(':', $variable, 2);
                    if(array_key_exists(1, $temp)){
                        $path_attribute[$nr] = $temp[0];
                    }
                }
                continue;
            }
            if(array_key_exists($nr, $attribute) === false){
                return false;
            }
            if(strtolower($part) != strtolower($attribute[$nr])){
                return false;
            }
        }
        if(!empty($path_attribute)){
            foreach($explode as $nr => $part){
                if(Route::is_variable($part)){
                    $variable = Route::get_variable($part);
                    if($variable){
                        $temp = explode(':', $variable, 2);
                        if(count($temp) === 2){
                            $attribute = $temp[0];
                            $type = ucfirst($temp[1]);
                            $className = '\\R3m\\Io\\Module\\Route\\Type' . $type;
                            $exist = class_exists($className);
                            if($exist){
                                $value = null;
                                d($select);
                                foreach($path_attribute as $path_nr => $path_value){
                                    if(
                                        $path_value == $attribute &&
                                        array_key_exists($path_nr, $select->attribute)
                                    ){
                                        $value = urldecode($select->attribute[$path_nr]);
                                        break;
                                    }
                                }
                                if($value){
                                    d($value);
                                    d($className);
                                    $validate = $className::validate($object, $value);
                                    d($validate);
                                    if(!$validate){
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    private static function is_match_by_condition($object, $route, $select){
        if(!property_exists($route, 'path')){
            return false;
        }
        $explode = explode('/', $route->path);
        array_pop($explode);
        $attribute = $select->attribute;
        if(empty($attribute)){
            return true;
        }
        foreach($explode as $nr => $part){
            if(Route::is_variable($part)){
                if(
                    property_exists($route, 'condition') &&
                    is_array($route->condition)
                ){
                    foreach($route->condition as $condition_nr => $value){
                        if(substr($value, 0, 1) == '!'){
                            //invalid conditions
                            if(strtolower(substr($value, 1)) == strtolower($attribute[$nr])){
                                return false;
                            }
                        } else {
                            //valid conditions
                            if(strtolower($value) == strtolower($attribute[$nr])){
                                return true;
                            }
                        }
                    }
                }
                continue;
            }
            if(array_key_exists($nr, $attribute) === false){
                return false;
            }
            if(strtolower($part) != strtolower($attribute[$nr])){
                return false;
            }
        }
        return true;
    }


    private static function is_match_by_method($object, $route, $select){
        if(!property_exists($route, 'method')){
            return true;
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
        $allowed_host_wildcard = [];
        $disallowed_host = [];
        foreach($select->host as $host){
            $host = strtolower($host);
            if(substr($host, 0, 1) == '!'){
                $disallowed_host[] = substr($host, 1);
                continue;
            }
            $allowed_host[] = $host;
            $explode = explode('.', $host);
            $explode[0] = '';
            $allowed_host_wildcard[] = implode('.', $explode);
        }
        foreach($route->host as $host){
            if(
                substr($host, 0, 1) === '!' ||
                substr($host, 0, 1) === '*'
            ){
                $host = substr($host, 1);
            }
            if(in_array($host, $disallowed_host)){
                return false;
            }
            if(in_array($host, $allowed_host)){
                return true;
            }
            if(in_array($host, $allowed_host_wildcard)){
                return true;
            }
        }
        return false;
    }

    private static function is_match_by_deep($object, $route, $select){
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
        $is_match = Route::is_match_by_method($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $route = Route::add_localhost($object, $route);
        $is_match = Route::is_match_by_host($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $is_match = Route::is_match_by_deep($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }

        $is_match = Route::is_match_by_attribute($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $is_match = Route::is_match_by_condition($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        return $is_match;
    }

    private static function is_match_has_slash_in_attribute($object, $route, $select){
        $is_match = Route::is_match_by_method($object, $route, $select);
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
        $is_match = Route::is_match_by_condition($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        return $is_match;
    }

    private static function is_match_by_wildcard($object, $route, $select){
        $is_match = Route::is_match_by_method($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $route = Route::add_localhost($object, $route);
        $is_match = Route::is_match_by_host($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        return $is_match;
    }

    private static function is_match_by_wildcard_has_slash_in_attribute($object, $route, $select){
        $is_match = Route::is_match_by_method($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        $route = Route::add_localhost($object, $route);
        $is_match = Route::is_match_by_host($object, $route, $select);
        if($is_match === false){
            return $is_match;
        }
        return $is_match;
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function configure(App $object){
        $config = $object->data(App::CONFIG);
        $url = $config->data(Config::DATA_PROJECT_DIR_DATA) . $config->data(Config::DATA_PROJECT_ROUTE_FILENAME);
        if(empty($config->data(Config::DATA_PROJECT_ROUTE_URL))){
            $config->data(Config::DATA_PROJECT_ROUTE_URL, $url);
        }
        $url = $config->data(Config::DATA_PROJECT_ROUTE_URL);
        $uuid = posix_geteuid();
        $cache_url = $config->data(Config::DATA_PROJECT_DIR_DATA) . 'Cache' . $config->data('ds') . $uuid . $config->data('ds') . $config->data(Config::DATA_PROJECT_ROUTE_FILENAME);
        $cache = Route::cache_read($object, $url, $cache_url);
        $cache = Route::cache_invalidate($object, $cache);
        if(empty($cache)){
            if(File::exist($url)){
                $read = File::read($url);
                $data = new Route(Core::object($read));
                $data->url($url);
                $data->cache_url($cache_url);
                $object->data(App::ROUTE, $data);
                Route::load($object);
                Route::framework($object);
                Route::cache_write($object);
            } else {
                $data = new Route();
                $data->url($url);
                $data->cache_url($cache_url);
                $object->data(App::ROUTE, $data);
                Route::load($object);
                Route::framework($object);
            }
        } else {
            $object->data(App::ROUTE, $cache);
        }
    }

    private static function cache_mtime($object, $cache){
        $time = strtotime(date('Y-m-d H:i:00'));
        if(File::mtime($cache->cache_url()) != $time){
            return File::touch($cache->cache_url(), $time, $time);
        }
    }

    private static function cache_invalidate($object, $cache){
        $has_resource = false;
        $invalidate = true;
        if(empty($cache)){
            return;
        }
        $time = strtotime(date('Y-m-d H:i:00'));
        if(
            File::exist($cache->cache_url()) &&
            $time == File::mtime($cache->cache_url())
        ){
            return $cache;
        }
        $data = $cache->data();
        foreach($data as $record){
            if(property_exists($record, 'resource')){
                $has_resource = true;
                if(!File::exist($record->resource)){
                    break;
                }
                if(!property_exists($record, 'mtime')){
                    break;
                }
                if(File::mtime($record->resource) != $record->mtime){
                    break;
                }
                continue;
            }
            $invalidate = false;
            break;
        }
        if(
            $invalidate &&
            $has_resource
        ){
            $cache_url = $cache->cache_url();
            if(File::exist($cache_url)){
                File::delete($cache_url);
            }
            return false;
        }
        elseif($has_resource === false) {
            $cache_url = $cache->cache_url();
            File::delete($cache_url);
            return false;
        } else {
            Route::cache_mtime($object, $cache);
            return $cache;
        }
    }

    private static function cache_read($object, $url, $cache_url){
        if(File::Exist($cache_url)){
            $read = File::read($cache_url);
            $data = new Route(Core::object($read));
            $data->url($url);
            $data->cache_url($cache_url);
            return $data;
        }
    }

    private static function cache_write($object){
        $config = $object->data(App::CONFIG);
        $route = $object->data(App::ROUTE);
        $data = $route->data();
        $result = new Data();
        $url = $route->url();
        $cache_url = $route->cache_url();
        $cache_dir = Dir::name($cache_url);
        $main = new stdClass();
        $main->resource = $url;
        $main->read = true;
        $main->mtime = File::mtime($url);
        $result->data(Core::uuid(), $main);
        foreach($data as $key => $record){
            if(property_exists($record, 'resource') === false){
                continue;
            }
            $result->data($key, $record);
        }
        foreach($data as $key => $record){
            if(property_exists($record, 'resource')){
                continue;
            }
            $result->data($key, $record);
        }
        $write = Core::object($result->data(), Core::OBJECT_JSON);
        Dir::create($cache_dir, Dir::CHMOD);
        $byte =  File::write($cache_url, $write);
        $time = strtotime(date('Y-m-d H:i:00'));
        $touch = File::touch($cache_url, $time, $time);
        return $byte;
    }

    private static function item_path($object, $item){
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

    private static function item_deep($object, $item){
        if(!property_exists($item, 'path')){
            $item->deep = 0;
            return $item;
        }
        $item->deep = substr_count($item->path, '/');
        return $item;
    }

    public static function load($object){
        $reload = false;
        $route = $object->data(App::ROUTE);
        if(empty($route)){
            return;
        }
        $data = $route->data();
        if(empty($data)){
            return;
        }
        foreach($data as $item){
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
                if(Core::object_is_empty($resource)){
                    throw new Exception('Could not read route file (' . $item->resource .')');
                }
                foreach($resource as $resource_key => $resource_item){
                    $check = $route->data($resource_key);
                    if(empty($check)){
                        $route->data($resource_key, $resource_item);
                    }
                }
                $reload = true;
                $item->read = true;
                $item->mtime = File::mtime($item->resource);
            } else {
                $item->read = false;
            }
        }
        if($reload === true){
            Route::load($object);
        }
    }

    private static function framework($object){
        $config = $object->data(App::CONFIG);
        $route = $object->data(App::ROUTE);
        $default_route = $config->data('framework.default.route');
        foreach($default_route as $record){
            $path = strtolower($record);
            $control = File::ucfirst(str_replace(':', '.', $record) . '.control');
            $control = substr($control, 0, -8);
            $attribute = 'r3m-io-cli-' . $path;
            $item = new stdClass();
            $item->path = $path . '/';
            $item->controller = 'R3m.Io.Cli.' . $control . '.Controller.' . $control . '.run';
            $item->language = 'en';
            $item->method = [
                "CLI"
            ];
            $item->deep = 1;
            $route->data($attribute, $item);
        }
    }

    public static function parse($object, $resource){
        $resource = str_replace([
            '{{',
            '}}'
        ], [
            '{',
            '}'
        ], $resource);
        $explode = explode('}', $resource, 2);
        if(!isset($explode[1])){
            return $resource;
        }
        $temp = explode('{', $explode[0], 2);
        if(isset($temp[1])){
            $attribute = substr($temp[1], 1);
            $config = $object->data(App::CONFIG);
            $value = $config->data($attribute);
            $resource = str_replace('{$' . $attribute . '}', $value, $resource);
            return Route::parse($object, $resource);
        } else {
            return $resource;
        }
    }
}