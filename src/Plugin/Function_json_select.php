<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;


function function_json_select(Parse $parse, Data $data, $url, $select=null){
    if(File::exist($url)){
        $read = File::read($url);
        $read = Core::object($read);
        $read = $parse->compile($read, [], $data, true);
        $json = new Data();
        $json->data($read);
        return $json->data($select);
    }
    return '';
}

