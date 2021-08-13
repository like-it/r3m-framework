<?php

use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use Exception;
use R3m\Io\Exception\FileMoveException;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

function function_environment_set(Parse $parse, Data $data, $environment=''){
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config.json';
    $read = $object->data_read($url);
    if(empty($read)){
        $read = new Data();
    }
    $read->data('framework.environment', $environment);
    try {
        File::write($url, Core::object($read->data(), Core::OBJECT_JSON));
    } catch (Exception | FileWriteException | ObjectException $exception){
        return $exception->getMessage() . PHP_EOL;
    }
    return ucfirst($environment) . ' mode enabled.' . PHP_EOL;
}

