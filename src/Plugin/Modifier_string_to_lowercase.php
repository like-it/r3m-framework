<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function modifier_string_to_lowercase(Parse $parse, Data $data, $value){
    return strtolower($value);
}

