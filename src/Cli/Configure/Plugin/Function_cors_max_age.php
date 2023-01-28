<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

use Exception;
use R3m\Io\Exception\FileAppendException;

/**
 * @throws Exception
 */
function function_cors_max_age(Parse $parse, Data $data, $age=null){
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
        throw new Exception('Only root & www-data can configure cors enable...');
    }
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config' . $object->config('extension.json');
    $config = $object->data_read($url);
    if(!$config){
        $config = new Data();
    }
    if($age === false) {
        $config->delete('server.cors.max-age');
        $config->write($url);
        return 'Cors max-age deleted.' . PHP_EOL;
    } else {
        $config->set('server.cors.max-age', $age);
        $config->write($url);
        return 'Cors max-age set.' . PHP_EOL;
    }
}

