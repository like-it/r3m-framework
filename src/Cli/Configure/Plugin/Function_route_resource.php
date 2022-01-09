<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;


function function_route_resource(Parse $parse, Data $data, $resource=''){
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Route' . $object->config('extension.json');
    $read = $object->data_read($url);
    $has_route = false;
    if(!$read){
        $read = new Data();
    }
    foreach($read->data() as $key => $route){
        if(
            property_exists($route, 'resource') &&
            stristr($route->resource, $resource) !== false
        ){
            $has_route = $route;
            break;
        }
    }
    if(!$has_route){
        $read->data(Core::uuid() . '.resource', $resource);
        $read->write($url);
        return 'Route resource: ' . $resource . ' added' . PHP_EOL;
    }

}

