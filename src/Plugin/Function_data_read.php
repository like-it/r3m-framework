<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_data_read(Parse $parse, Data $data, $url=''){
    if(\R3m\Io\Module\File::exist($url)){
        $read = \R3m\Io\Module\File::read($url);
        $read = \R3m\Io\Module\Core::object($read);
        $data->data(\R3m\Io\Module\Core::object_merge($data->data(),$read));
        $read = $parse->compile($data->data(), [], $data, 'css');
        return $read;
    }
    return '';
}
