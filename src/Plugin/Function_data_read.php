<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;

function function_data_read(Parse $parse, Data $data, $url=''){
    if(File::exist($url)){
        $read = File::read($url);
        $read = Core::object($read);
        $read = $parse->compile($read, [], $data, true);
        $data->data($read);
        return $read;
    }
    return '';
}
