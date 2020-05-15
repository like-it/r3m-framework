<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_str_repeat(Parse $parse, Data $data, $input, $multiplier=0){
    $multiplier = abs($multiplier);
    $result = str_repeat($input, $multiplier);
    return $result;
}