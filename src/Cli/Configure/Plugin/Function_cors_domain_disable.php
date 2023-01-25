<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

use Exception;
use R3m\Io\Exception\FileAppendException;

/**
 * @throws Exception
 */
function function_cors_domain_disable(Parse $parse, Data $data, $host=''){
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
    if(in_array($host, $list)){
        foreach($list as $key => $value){
            if($value === $host){
                unset($list[$key]);
            }
        }
        $list = array_values($list);
    }
    $config->set('server.cors.domains', $list);
    $config->write($url);
    return 'Cors disabled for host: ' . $host . PHP_EOL;
}

