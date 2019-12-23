<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_request(Parse $parse, Data $data, $attribute=null, $value=null){
    $object = $parse->object();
    return $object->data('R3m\\Io.Request');
//     d($object->data('R3m\\Io.Request'));
//     dd($url);
}
