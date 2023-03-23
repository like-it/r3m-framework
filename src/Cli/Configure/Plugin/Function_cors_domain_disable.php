<?php

use R3m\Io\Config;

use R3m\Io\Module\Dir;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Event;

/**
 * @throws Exception
 */
function function_cors_domain_disable(Parse $parse, Data $data, $domain=''){
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
        $exception = new Exception('Only root & www-data can configure cors domain disable...');
        Event::trigger($object, 'cli.configure.cors.domain.disable', [
            'domain' => $domain,
            'exception' => $exception
        ]);
        throw $exception;
    }
    if(empty($domain)){
        $exception = new Exception('Domain cannot be empty...');
        Event::trigger($object, 'cli.configure.cors.domain.disable', [
            'domain' => $domain,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $object = $parse->object();
    $dir = $object->config('project.dir.data');
    $url = $dir .
        'Config' .
        $object->config('extension.json')
    ;
    $config = $object->data_read($url);
    if(!$config){
        $config = new Data();
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            Dir::create($dir, 0777);
        } else {
            Dir::create($dir, 0750);
        }
        if(empty($id)){
            exec('chown www-data:www-data ' . $dir);
        }
    }
    $list = $config->get('server.cors.domains');
    if(empty($list)){
        $list = [];
    }
    if(in_array($domain, $list)){
        foreach($list as $key => $value){
            if($value === $domain){
                unset($list[$key]);
            }
        }
        $list = array_values($list);
    }
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
    $response = 'Cors disabled for domain: ' . $domain . PHP_EOL;
    Event::trigger($object, 'cli.configure.cors.domain.disable', [
        'domain' => $domain
    ]);
    return $response;
}

