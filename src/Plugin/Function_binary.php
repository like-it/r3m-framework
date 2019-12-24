<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_binary(Parse $parse, Data $data){
    if(array_key_exists('_', $_SERVER)){
        $dirname = \R3m\Io\Module\Dir::name($_SERVER['_']);
        return str_replace($dirname, '', $_SERVER['_']);
    }
}
