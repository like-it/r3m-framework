<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_object_new(Parse $parse, Data $data){
    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);
    $name = array_shift($attribute);
    if(empty($name)){
        $name = '\stdClass';
    }
    $count = count($attribute);
    switch($count){
        case 1 :
            return new $name(array_shift($attribute));
        break;
        default :
            return new $name();
        break;
    }
}

