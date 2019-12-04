<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_implode(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    $glue = array_shift($attribute);
    $array = array_shift($attribute);
    return implode($glue, $array);
}

