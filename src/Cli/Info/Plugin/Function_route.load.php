<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Config;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

function function_route_load(Parse $parse, Data $data){
    $object = $parse->object();
    $route = $object->data(App::ROUTE);
    dd($route);
}
