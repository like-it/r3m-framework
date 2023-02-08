<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;

function function_server_token(Parse $parse, Data $data){
    if(array_key_exists('HTTP_AUTHORIZATION', $_SERVER)){
        $explode = explode('Bearer ', $_SERVER['HTTP_AUTHORIZATION'], 2);
        if(array_key_exists(1, $explode)){
            return $explode[1];
        }
    }
}
