<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Core;

use Exception;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

/**
 * @throws Exception
 */
function function_server_admin(Parse $parse, Data $data, $email=''){
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
        throw new Exception('Only root and www-data can configure route add...');
    }
    if(empty($email)){
        throw new Exception('Server admin e-mail cannot be empty');
    }
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config.json';
    $read = $object->data_read($url);
    if(empty($read)){
        $read = new Data();
    }
    $read->data('server.admin', $email);
    $write = '';
    try {
        $write = File::write($url, Core::object($read->data(), Core::OBJECT_JSON));
        if($id === 0){
            File::chmod($url, 0666);
            $project_dir_data = $object->config('project.dir.data');
            Core::execute('chown www-data:www-data -R ' . $project_dir_data);
            if(File::exist($project_dir_data . 'Cache/0/')){
                Core::execute('chown root:root -R ' . $project_dir_data . 'Cache/0/');
            }
            if(File::exist($project_dir_data . 'Compile/0/')){
                Core::execute('chown root:root -R ' . $project_dir_data . 'Compile/0/');
            }
            if(File::exist($project_dir_data . 'Cache/1000/')){
                Core::execute('chown 1000:1000 -R ' . $project_dir_data . 'Cache/1000/');
            }
            if(File::exist($project_dir_data . 'Compile/1000/')){
                Core::execute('chown 1000:1000 -R ' . $project_dir_data . 'Compile/1000/');
            }
        }
    } catch (Exception | FileWriteException | ObjectException $exception){
        return $exception;
    }
    return 'Bytes written: ' . $write . "\n";
}

