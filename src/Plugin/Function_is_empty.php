<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_is_empty(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    foreach($attribute as $nr => $is_empty){
        if(!empty($is_empty)){
            return false;
        }
    }
    return true;
}

