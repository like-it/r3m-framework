<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;


function function_route_delete(Parse $parse, Data $data, $resource=''){
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Route' . $object->config('extension.json');
    $read = $object->data_read($url);
    if($read){
        foreach($read->data() as $key => $route){
            if(
                property_exists($route, 'resource') &&
                stristr($route->resource, $resource) !== false
            ){
                $read->data('delete', $key);
            }
        }
        $read->write($url);
    }
}

