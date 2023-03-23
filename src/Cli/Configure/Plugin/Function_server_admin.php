<?php

use R3m\Io\Config;

use R3m\Io\Module\Dir;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Core;
use R3m\Io\Module\Event;

use Exception;

use R3m\Io\Exception\ObjectException;

/**
 * @throws Exception
 */
function function_server_admin(Parse $parse, Data $data, $email=''){
    $object = $parse->object();
    $write = 0;
    $id = $object->config(Config::POSIX_ID);
    if(
        !in_array(
            $id,
            [
                0,
                33
            ],
            true
        )
    ){
        $exception = new Exception('Only root and www-data can configure route add...');
        Event::trigger($object, 'configure.server.admin', [
            'email' => $email,
            'bytes' => $write,
            'exception' => $exception
        ]);
        throw $exception;
    }
    if(empty($email)){
        $exception = new Exception('Server admin e-mail cannot be empty');
        Event::trigger($object, 'configure.server.admin', [
            'email' => $email,
            'bytes' => $write,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $object = $parse->object();
    $dir = $object->config('project.dir.data');
    $url = $object->config('project.dir.data') . 'Config.json';
    $read = $object->data_read($url);
    if(empty($read)){
        $read = new Data();
        Dir::create($dir, Dir::CHMOD);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 777 ' . $dir);
        }
        if(empty($id)){
            exec('chown www-data:www-data ' . $url);
        }
    }
    $read->data('server.admin', $email);
    try {
        $write = File::write($url, Core::object($read->data(), Core::OBJECT_JSON));
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 666 ' . $url);
        } else {
            exec('chmod 640 ' . $url);
        }
        if(empty($id)){
            exec('chown www-data:www-data ' . $url);
        }
        Event::trigger($object, 'configure.server.admin', [
            'email' => $email,
            'bytes' => $write,
        ]);
    } catch (Exception | ObjectException $exception){
        Event::trigger($object, 'configure.server.admin', [
            'email' => $email,
            'bytes' => $write,
            'exception' => $exception
        ]);
        return $exception;
    }
    return 'Bytes written: ' . $write . "\n";
}

