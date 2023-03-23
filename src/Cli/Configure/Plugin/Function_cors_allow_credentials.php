<?php

use R3m\Io\Config;

use R3m\Io\Module\Dir;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Event;

/**
 * @throws Exception
 */
function function_cors_allow_credentials(Parse $parse, Data $data, $allow=null){
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
        $exception = new Exception('Only root & www-data can configure cors enable...');
        Event::trigger($object, 'configure.cors.allow_credentials', [
            'allow' => $allow,
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
    if($allow === true){
        $config->set('server.cors.allow_credentials', $allow);
        $config->write($url);
        if(empty($id)){
            exec('chown www-data:www-data ' . $url);
        }
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 666 ' . $url);
        } else {
            exec('chmod 640 ' . $url);
        }
        $response = 'Cors allow credentials enabled.' . PHP_EOL;
    }
    elseif($allow === false){
        $config->delete('server.cors.allow_credentials');
        $config->write($url);
        if(empty($id)){
            exec('chown www-data:www-data ' . $url);
        }
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 666 ' . $url);
        } else {
            exec('chmod 640 ' . $url);
        }
        $response = 'Cors allow credentials disabled.' . PHP_EOL;
    }
    Event::trigger($object, 'configure.cors.allow_credentials', [
        'allow' => $allow
    ]);
    return $response;
}

