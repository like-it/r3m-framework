<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;


function function_host_delete(Parse $parse, Data $data){
    $attribute = func_get_args();
    array_shift($attribute);
    array_shift($attribute);
    $host = array_shift($attribute);
    $url = '/etc/hosts';
    $data = explode("\n", File::read($url));
    foreach($data as $nr => $row){
        if(stristr($row, $host) !== false){
            unset($data[$nr]);
        }
    }
    $data = implode("\n", $data);
    File::write($url, $data);
}

