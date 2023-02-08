<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

use Exception;

/**
 * @throws Exception
 */
function function_cors_methods(Parse $parse, Data $data, $method=''){
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
        throw new Exception('Only root & www-data can configure cors setup default...');
    }
    if(empty($method)){
        throw new Exception('Method cannot be empty...');
    }
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config' . $object->config('extension.json');
    $config = $object->data_read($url);
    if(!$config){
        $config = new Data();
    }
    $config->set('server.cors.methods', $method);
    $config->write($url);
    if($id === 0){
        File::chown($url, 'www-data', 'www-data');
    }
    return 'Cors methods updated.' . PHP_EOL;
}

