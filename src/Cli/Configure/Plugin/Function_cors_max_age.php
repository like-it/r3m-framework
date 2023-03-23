<?php

use R3m\Io\Config;

use R3m\Io\Module\Dir;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Event;

use Exception;

/**
 * @throws Exception
 */
function function_cors_max_age(Parse $parse, Data $data, $age=null){
    $object = $parse->object();
    $id = $object->config(Config::POSIX_ID);
    $id = posix_geteuid();
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
        $exception = new Exception('Only root & www-data can configure cors enable...');
        Event::trigger($object, 'configure.cors.max-age', [
            'age' => $age,
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
    $response = null;
    if($age === false) {
        $config->delete('server.cors.max-age');
        $config->write($url);
        $response = 'Cors max-age deleted.' . PHP_EOL;
    } else {
        $config->set('server.cors.max-age', $age);
        $config->write($url);
        $response = 'Cors max-age set.' . PHP_EOL;
    }
    if(empty($id)){
        exec('chown www-data:www-data ' . $url);
    }
    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
        exec('chmod 666 ' . $url);
    } else {
        exec('chmod 640 ' . $url);
    }
    Event::trigger($object, 'configure.cors.max-age', [
        'age' => $age
    ]);
    return $response;
}

