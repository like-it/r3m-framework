<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_response_object(Parse $parse, Data $data){
    $object = $parse->object();
    $object->config('response.output', 'object');
}
