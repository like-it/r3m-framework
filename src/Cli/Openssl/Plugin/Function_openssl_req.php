<?php

use R3m\Io\App;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;

function function_openssl_req(Parse $parse, Data $data){
    $object = $parse->object();

    $url_cert  = App::parameter($object, 'out', 1);
    $url_key  = App::parameter($object, 'keyout', 1);

    $dir_cert = Dir::name($url_cert);
    $dir_key = Dir::name($url_key);

    Dir::create($dir_cert);
    Dir::create($dir_key);

    $request = (array) $object->request();
    $execute = implode(' ', $request);
    $output = [];
    Core::execute($execute, $output);
    echo implode(PHP_EOL, $output);
}
