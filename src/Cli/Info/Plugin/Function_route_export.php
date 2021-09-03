<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Route;

function function_route_export(Parse $parse, Data $data){
    $object = $parse->object();
    $route = $object->data(App::ROUTE);
    $list = $route->data();
    $result = [];
    foreach($list as $nr => $record){
        $result[$nr] = Route::controller($record);
    }
    return $result;
}
