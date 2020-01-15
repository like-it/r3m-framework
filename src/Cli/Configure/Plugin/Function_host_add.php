<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_host_add(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    $ip = array_shift($attribute);
    $host = array_shift($attribute);

    $url = '/etc/hosts';
    $data = $ip . "\t" . $host . "\n";
    $append = \R3m\Io\Module\File::append($url, $data);
}

