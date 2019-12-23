<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_require(Parse $parse, Data $data, $url=''){
    $read = '';
    if(File::exist($url)){
        $read = \R3m\Io\Module\File::read($url);
    } else {
        throw new Exception('Require: file not found: ' . $url);
    }
    return $parse->compile($read, [], $data);
}

