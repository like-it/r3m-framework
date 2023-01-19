<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

use Exception;
use R3m\Io\Exception\FileAppendException;

/**
 * @throws Exception
 */
function function_cors_enable(Parse $parse, Data $data, $host=''){
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
    if(empty($host)){
        throw new Exception('Host cannot be empty...');
    }
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config' . $object->config('extension.json');
    $config = $object->data_read($url);
    if(!$config){
        $config = new Data();
    }
    $list = $config->get('server.cors.domains');
    if(empty($list)){
        $list = [];
    }
    $list[] = $host;
    $list = array_unique($list);
    $config->set('server.cors.domains', $list);
    $config->write($url);
    return 'Cors enabled for host: ' . $host . PHP_EOL;
}

