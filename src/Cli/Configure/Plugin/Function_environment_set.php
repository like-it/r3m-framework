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
function function_environment_set(Parse $parse, Data $data, $environment=''){
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
        $exception = new Exception('Only root & www-data can configure environment set...');
        Event::trigger($object, 'cli.configure.framework.environment.set', [
            'environment' => $environment,
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
    $read->data('framework.environment', $environment);
    try {
        File::write($url, Core::object($read->data(), Core::OBJECT_JSON));
        if(empty($id)){
            exec('chown www-data:www-data ' . $url);
        }
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 666 ' . $url);
        } else {
            exec('chmod 640 ' . $url);
        }
        Event::trigger($object, 'cli.configure.framework.environment.set', [
            'environment' => $environment
        ]);
    } catch (Exception $exception){
        Event::trigger($object, 'cli.configure.framework.environment.set', [
            'environment' => $environment,
            'exception' => $exception
        ]);
        return $exception;
    }
    return ucfirst($environment) . ' mode enabled.' . PHP_EOL;
}

