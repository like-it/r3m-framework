<?php

use R3m\Io\Config;

use R3m\Io\Module\Dir;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Event;

/**
 * @throws Exception
 */
function function_cors_setup_default(Parse $parse, Data $data, $host=''){
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
        $exception = new Exception('Only root & www-data can configure cors setup default...');
        Event::trigger($object, 'cli.configure.cors.setup.default', [
            'host' => $host,
            'exception' => $exception
        ]);
        throw $exception;
    }
    if(empty($host)){
        $exception = new Exception('Host cannot be empty...');
        Event::trigger($object, 'cli.configure.cors.setup.default', [
            'host' => $host,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $url = $object->config('app.config.url');
    $dir = Dir::name($url);
    $config = $object->data_read($url);
    if(!$config){
        $config = new Data();
        Dir::create($dir, Dir::CHMOD);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 777 '  . $dir);
        }
        if(empty($id)){
            exec('chown www-data:www-data ' . $dir);
        }
    }
    $list = [];
    $list[] = $host;
    $config->delete('server.cors');
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
    if(empty($id)){
        exec('chmod www-data:www-data ' . $url);
    }
    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
        exec('chmod 666 ' . $url);
    } else {
        exec('chmod 640 ' . $url);
    }
    $response = 'Cors setup default for host: ' . $host . PHP_EOL;
    Event::trigger($object, 'cli.configure.cors.setup.default', [
        'host' => $host
    ]);
    return $response;
}

