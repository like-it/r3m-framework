<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function capture_append(Parse $parse, Data $data, $name, $value=null){
    $list = $data->data($name);
    if(empty($list)){
        $list = [];
    }
    $list[] = $value;
    $data->data($name, $list);
    return '';
}

