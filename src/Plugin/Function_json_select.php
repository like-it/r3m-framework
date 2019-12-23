<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_json_select(Parse $parse, Data $data, $url, $select=null){
    if(\R3m\Io\Module\File::exist($url)){
        $read = \R3m\Io\Module\File::read($url);
        $read = \R3m\Io\Module\Core::object($read);
        $read = $parse->compile($read, [], $data, true);
        $json = new Data();
        $json->data($read);
        return $json->data($select);
    }
    return '';
}

