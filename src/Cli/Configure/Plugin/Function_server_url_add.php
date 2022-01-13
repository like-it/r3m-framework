<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Core;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

function function_server_url_add(Parse $parse, Data $data, stdClass $node){
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config.json';
    $read = $object->data_read($url);
    if(empty($read)){
        $read = new Data();
    }
    $read->data('server.url.' . $node->name + '.' . $node->environment, $node->url);
    $write = '';
    try {
        $write = File::write($url, Core::object($read->data(), Core::OBJECT_JSON));
    } catch (Exception | FileWriteException | ObjectException $exception){
        echo $exception->getMessage();
    }
    return 'Bytes written: ' . $write . "\n";
}

