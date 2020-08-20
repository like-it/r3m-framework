<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function modifier_string_to_uppercase_first(Parse $parse, Data $data, $value){
    return ucfirst($value);
}

