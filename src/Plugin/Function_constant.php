<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_constant(Parse $parse, Data $data, $name, $value=null){
    if($value !== null){
        define(strtoupper(str_replace('.','_', $name)), $value);
    }
    $name = strtoupper(str_replace('.','_', $name));
    if(defined($name)){
        return constant($name);
    }
    return '';
}

