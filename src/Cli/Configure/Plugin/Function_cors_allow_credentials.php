<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

use Exception;
use R3m\Io\Exception\FileAppendException;

/**
 * @throws Exception
 */
function function_cors_allow_credentials(Parse $parse, Data $data, $allow=null){
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
    if($allow === true){
        $config->set('server.cors.allow_credentials', $allow);
        $config->write($url);
        return 'Cors allow credentials enabled.' . PHP_EOL;
    }
    elseif($allow === false){
        $config->delete('server.cors.allow_credentials');
        $config->write($url);
        return 'Cors allow credentials disabled.' . PHP_EOL;
    }
}

