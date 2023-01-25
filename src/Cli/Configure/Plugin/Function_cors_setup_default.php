<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

use Exception;

/**
 * @throws Exception
 */
function function_cors_setup_default(Parse $parse, Data $data, $host=''){
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
    if(empty($host)){
        throw new Exception('Host cannot be empty...');
    }
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config' . $object->config('extension.json');
    $config = $object->data_read($url);
    if(!$config){
        $config = new Data();
    }
    $list = [];
    $list[] = $host;
    $config->set('server.cors.allow_credentials', true);
    $config->set('server.cors.methods', [
        "GET",
        "POST",
        "PATCH",
        "PUT",
        "DELETE",
        "OPTIONS"
    ]);
    $config->set('server.cors.headers.allow', [
        "Origin",
        "Cache-Control",
        "Content-Type",
        "Authorization",
        "X-Requested-With"
    ]);
    $config->set('server.cors.headers.expose', [
        "Cache-Control",
        "Content-Language",
        "Content-Length",
        "Content-Type",
        "Expires",
        "Last-Modified",
        "Pragma"
    ]);
    $config->set('server.cors.domains', $list);
    $config->set('server.cors.max-age', 86400);
    $config->write($url);
    if($id === 0){
        File::chown($url, 'www-data', 'www-data');
    }
    return 'Cors setup default for host: ' . $host . PHP_EOL;
}

