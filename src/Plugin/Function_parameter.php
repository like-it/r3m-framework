<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_parameter(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    $name = array_shift($attribute);
    $offset = array_shift($attribute);

    $object = $parse->object();
    $parameter = $object->parameter($object, $name, $offset);

    return $parameter;
}

