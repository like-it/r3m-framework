<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;


function function_route_resource(Parse $parse, Data $data, $resource=''){
    $object = $parse->object();
    $read = $object->data(App::ROUTE);
    $has_route = false;
    if($read){
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
            dd(App::ROUTE);

            $read = $object->data_read($has_route->resource);
            $key = $add->name;
            unset($add->resource);
            unset($add->name);
            $read->data($key, $add);
            $read->write($has_route->resource);
            return 'Route: ' . $key . ' added' . PHP_EOL;
        }
    }
}

