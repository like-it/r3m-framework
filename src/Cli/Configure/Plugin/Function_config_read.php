<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_config_read(Parse $parse, Data $data, $attribute=''){
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config.json';
    $read = $object->data_read($url);
    if(!empty($read)){
        return $read->data($attribute);
    }
}

