<?php

use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use Exception;

/**
 * @throws Exception
 */
function function_environment_toggle(Parse $parse, Data $data){
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
        $exception = new Exception('Only root & www-data can configure environment toggle...');
        Event::trigger($object, 'cli.configure.framework.environment.set', [
            'environment' => false,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $dir = $object->config('project.dir.data');
    $url = $dir .
        'Config' .
        $object->config('extension.json')
    ;
    $read = $object->data_read($url);
    if(empty($read)){
        $read = new Data();
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            Dir::create($dir, 0777);
        } else {
            Dir::create($dir, 0750);
        }
        if(empty($id)){
            exec('chown www-data:www-data ' . $dir);
        }
    }
    $is_development = $read->data('framework.environment');
    if($is_development == Config::MODE_DEVELOPMENT){
        $read->data('framework.environment', Config::MODE_STAGING);
        $status = Config::MODE_STAGING;
    }
    elseif($is_development == Config::MODE_STAGING){
        $read->data('framework.environment', Config::MODE_PRODUCTION);
        $status = Config::MODE_PRODUCTION;
    }
    else {
        $read->data('framework.environment', Config::MODE_DEVELOPMENT);
        $status = Config::MODE_DEVELOPMENT;
    }
    try {
        File::write($url, Core::object($read->data(), Core::OBJECT_JSON));
        if(empty($id)){
            exec('chmod www-data:www-data ' . $url);
        }
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 666 ' . $url);
        } else {
            exec('chmod 640 ' . $url);
        }
        Event::trigger($object, 'cli.configure.framework.environment.set', [
            'environment' => $status
        ]);
    } catch (Exception $exception){
        Event::trigger($object, 'cli.configure.framework.environment.set', [
            'environment' => $status,
            'exception' => $exception
        ]);
        return $exception;
    }
    if($status == Config::MODE_PRODUCTION){
        return 'Production mode enabled.' . PHP_EOL;
    }
    elseif($status == Config::MODE_STAGING){
        return 'Staging mode enabled.' . PHP_EOL;
    }
    else {
        return 'Development mode enabled.' . PHP_EOL;
    }
}

