<?php

use R3m\Io\Config;

use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

use Exception;

/**
 * @throws Exception
 */
function function_cors_headers_allow(Parse $parse, Data $data, $headers=''){
    $object = $parse->object();
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
        $exception = new Exception('Only root & www-data can configure cors headers allow...');
        Event::trigger($object, 'cli.configure.cors.headers.allow', [
            'headers' => $headers,
            'exception' => $exception
        ]);
        throw $exception;
    }
    if(empty($headers)){
        $exception = new Exception('Headers cannot be empty...');
        Event::trigger($object, 'cli.configure.cors.headers.allow', [
            'headers' => $headers,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $dir = $object->config('project.dir.data');
    $url = $dir .
        'Config' .
        $object->config('extension.json')
    ;
    $config = $object->data_read($url);
    if(!$config){
        $config = new Data();
        Dir::create($dir, Dir::CHMOD);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 777 ' . $dir);
        }
        if(empty($id)){
            exec('chown www-data:www-data ' . $dir);
        }
    }
    $config->set('server.cors.headers.allow', $headers);
    $config->write($url);
    if(empty($id)){
        exec('chmod www-data:www-data ' . $url);
    }
    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
        exec('chmod 666 ' . $url);
    } else {
        exec('chmod 640 ' . $url);
    }
    $response = 'Headers allow updated.' . PHP_EOL;
    Event::trigger($object, 'cli.configure.cors.headers.allow', [
        'headers' => $headers
    ]);
    return $response;
}

