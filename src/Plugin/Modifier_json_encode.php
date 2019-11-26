<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function modifier_json_encode(Parse $parse, Data $data, $value, $options=0, $depth=512){
    $options += 0;
    return json_encode($value, $options, $depth);
}

