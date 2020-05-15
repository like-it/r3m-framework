<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_route_get(Parse $parse, Data $data, $name=null, $options=[]){

    $object = $parse->object();
    $url = $object->data(\R3m\Io\App::ROUTE)::get($object, $name, $options);
    return $url;
}
