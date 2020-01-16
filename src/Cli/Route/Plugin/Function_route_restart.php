<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_route_restart(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    $object = $parse->object();
    $route = $object->data(\R3m\Io\App::ROUTE);
    $cache_url = $route->cache_url();
    if(\R3m\Io\Module\File::exist($cache_url)){
        \R3m\Io\Module\Dir::remove($cache_url);
    }
}
