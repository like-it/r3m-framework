<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;


function function_route_add(Parse $parse, Data $data, $add=''){
    $object = $parse->object();
//    $url = $object->config('project.dir.data') . 'Route' . $object->config('extension.json');
//    $read = $object->parse_read($url);
    $read = $object->data(App::ROUTE);
    d($read);
    $has_route = false;
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
            $key = $add->name;
            unset($add->resource);
            unset($add->name);
            $read->data($key, $add);
            $read->write($has_route->resource);
            return 'Route: ' . $key . ' added' . PHP_EOL;
        } else {
            $error[] = 'Resource not found, available resources:';
            $is_resource = false;
            foreach($read->data() as $key => $route){
                if(property_exists($route, 'resource')){
                    $error[] = 'resource: ' . $route->resource;
                    $is_resource = true;
                }
            }
            if($is_resource === false){
                $error[] = 'No resources found, is Route corrupt?';
            }
            throw new Exception(implode(PHP_EOL, $error));
        }
    }
}

