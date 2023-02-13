<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Core;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

function function_server_url_add(Parse $parse, Data $data, stdClass $node){
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
        throw new Exception('Only root and www-data can configure server url add...');
    }
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config.json';
    $read = $object->data_read($url);
    if(empty($read)){
        $read = new Data();
    }
    $read->data('server.url.' . $node->name . '.' . $node->environment, $node->url);
    $write = '';
    try {
        $write = File::write($url, Core::object($read->data(), Core::OBJECT_JSON));
        if($id === 0){
            File::chmod($url, 0666);
            $project_dir_data = $object->config('project.dir.data');
            Core::execute($object, 'chown www-data:www-data -R ' . $project_dir_data);
            if(File::exist($project_dir_data . 'Cache/0/')){
                Core::execute($object, 'chown root:root -R ' . $project_dir_data . 'Cache/0/');
            }
            if(File::exist($project_dir_data . 'Compile/0/')){
                Core::execute($object, 'chown root:root -R ' . $project_dir_data . 'Compile/0/');
            }
            if(File::exist($project_dir_data . 'Cache/1000/')){
                Core::execute($object, 'chown 1000:1000 -R ' . $project_dir_data . 'Cache/1000/');
            }
            if(File::exist($project_dir_data . 'Compile/1000/')){
                Core::execute($object, 'chown 1000:1000 -R ' . $project_dir_data . 'Compile/1000/');
            }
        }
    } catch (Exception | FileWriteException | ObjectException $exception){
        echo $exception;
    }
    return 'Bytes written: ' . $write . "\n";
}

