<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function modifier_default(Parse $parse, Data $data, $value, $default=null){
    if(empty($value)){
        return $default;
    }
    return $value;
}

