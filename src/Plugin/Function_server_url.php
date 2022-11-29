<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Config;

function function_server_url(Parse $parse, Data $data, $name=''){
    $object = $parse->object();
    $name = str_replace('.', '-', $name);
    return $object->config('server.url.' . $name . '.' . $object->config('framework.environment'));
}
