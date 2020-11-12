<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

function function_route_restart(Parse $parse, Data $data){
    $object = $parse->object();
    $route = $object->data(\R3m\Io\App::ROUTE);
    $cache_url = $route->cache_url();
    if(File::exist($cache_url)){
        File::delete($cache_url);
    }
}
