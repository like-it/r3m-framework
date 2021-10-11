<?php
namespace R3m\Io\Module\Compile;

/**
 * @copyright                (c) Remco van der Velde 2019 - 2021
 * @version                  0.1.45
 * @license                  MIT
 * @note                     Auto generated file, do not modify!
 * @author                   R3m\Io\Module\Parse\Build
 * @author                   Remco van der Velde
 * @source                   /mnt/c/Repository/r3m-framework/src/Cli/Configure/View/Route.Add.tpl
 * @generation-date          2021-10-11 14:53:39
 * @generation-time          63.6 msec
 */

use Exception;
use stdClass;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Filter;
use R3m\Io\Module\Handler;
use R3m\Io\Module\Host;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Route;
use R3m\Io\Module\Sort;
use R3m\Io\Module\Template\Main;
use R3m\Io\Exception\AuthenticationException;
use R3m\Io\Exception\AuthorizationException;
use R3m\Io\Exception\ErrorException;
use R3m\Io\Exception\FileAppendException;
use R3m\Io\Exception\FileMoveException;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\PluginNotFoundException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Template_Route_Add_b613d894f901447efc35a5005a2980b81795a4f4 extends Main {

    public function run(){
        ob_start();
        $this->parse()->is_assign(true);
        $this->storage()->data('json', $this->function_parameter($this->parse(), $this->storage(), 'add', 1));
        $this->parse()->is_assign(false);
        echo '';
        $this->parse()->is_assign(true);
        $this->storage()->data('route', $this->modifier_json_decode($this->parse(), $this->storage(), $this->storage()->data('json')));
        $this->parse()->is_assign(false);
        echo '';
        $method = $this->function_route_add($this->parse(), $this->storage(), $this->storage()->data('route'));
        if (is_object($method)){ return $method; }
        elseif (is_array($method)){ return $method; }
        else { echo $method; }
        return ob_get_clean();
    }

    private function modifier_json_decode(Parse $parse, Data $data, $value, $associative=false){
        return json_decode($value, $associative);
    }

    private function function_parameter(Parse $parse, Data $data, $name=null, $offset=null){
        $object = $parse->object();
        return $object->parameter($object, $name, $offset);
    }

    private function function_route_add(Parse $parse, Data $data, $add=''){
        $object = $parse->object();
        $url = $object->config('project.dir.data') . 'Route' . $object->config('extension.json');
        $read = $object->parse_read($url);
        $has_route = false;
        d($add);
        if($read){
            foreach($read->data() as $key => $route){
                if(
                    property_exists($route, 'resource') &&
                    property_exists($add, 'resource') &&
                    stristr($route->resource, $add->resource) !== false
                ){
                    $has_route = $route;
                    break;
                }
            }
            if($has_route){
                $read = $object->data_read($has_route->resource);
                d($add);
                dd($read);
            } else {
                $error[] = 'Resource not found, available resources:';
                    foreach($read->data() as $key => $route){
                        if(property_exists($route, 'resource')){
                            $error[] = 'resource: ' . $route->resource;
                        }
                    }
                    throw new Exception(implode(PHP_EOL, $error));
                }
        }
    }


// R3M-IO-a10eadbe-078a-4a68-a943-8c0531e5739e
}