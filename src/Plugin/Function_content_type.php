<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_content_type(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    return $parse->object()->data(\R3m\Io\App::CONTENT_TYPE);
}
