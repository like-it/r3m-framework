<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_date(Parse $parse, Data $data, $format, $timestamp=null){
    if($timestamp === null){
        $timestamp = time();
    }
    return date($format, $timestamp);
}
