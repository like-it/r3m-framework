<?php

use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use Exception;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\LocateException;


function function_environment_set(Parse $parse, Data $data, $environment=''){
    $id = posix_geteuid();
    if(
        !in_array(
            $id,
            [
                0,
                33
            ]
        )
    ){
        throw new Exception('Only root & www-data can configure domain add...');
    }
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config.json';
    $read = $object->data_read($url);
    if(empty($read)){
        $read = new Data();
    }
    $read->data('framework.environment', $environment);
    try {
        File::write($url, Core::object($read->data(), Core::OBJECT_JSON));
        Event::trigger($object, 'framework.environment.set', [
            'environment' => $environment
        ]);
    } catch (Exception | FileWriteException | ObjectException | LocateException $exception){
        return $exception;
    }
    return ucfirst($environment) . ' mode enabled.' . PHP_EOL;
}

