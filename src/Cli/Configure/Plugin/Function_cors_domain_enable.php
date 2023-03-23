<?php

use R3m\Io\Config;

use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

use Exception;

/**
 * @throws Exception
 */
function function_cors_domain_enable(Parse $parse, Data $data, $domain=''){
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
        $exception = new Exception('Only root & www-data can configure cors domain enable...');
        Event::trigger($object, 'configure.cors.domain.enable', [
            'domain' => $domain,
            'exception' => $exception
        ]);
        throw $exception;
    }
    if(empty($domain)){
        throw new Exception('Domain cannot be empty...');
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
    $list = $config->get('server.cors.domains');
    if(empty($list)){
        $list = [];
    }
    $list[] = $domain;
    $list = array_unique($list);
    $config->set('server.cors.domains', $list);
    $config->write($url);
    if(empty($id)){
        exec('chmod www-data:www-data ' . $url);
    }
    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
        exec('chmod 666 ' . $url);
    } else {
        exec('chmod 640 ' . $url);
    }
    $response = 'Cors enabled for domain: ' . $domain . PHP_EOL;
    Event::trigger($object, 'configure.cors.domain.enable', [
        'domain' => $domain
    ]);
    return $response;
}

