<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_session(Parse $parse, Data $data, $attribute=null, $value=null){

    $session = \R3m\Io\Module\Handler::session($attribute, $value);
    return \R3m\Io\Module\Core::object($session);
}

