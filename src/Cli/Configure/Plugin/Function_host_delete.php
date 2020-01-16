<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_host_delete(Parse $parse, Data $data){
    $attribute = func_get_args();
    array_shift($attribute);
    array_shift($attribute);
    $host = array_shift($attribute);
    $url = '/etc/hosts';
    $data = explode("\n", \R3m\Io\Module\File::read($url));
    foreach($data as $nr => $row){
        if(stristr($row, $host) !== false){
            unset($data[$nr]);
        }
    }
    $data = implode("\n", $data);
    \R3m\Io\Module\File::write($url, $data);
}

