<?php

use R3m\Io\Config;

use R3m\Io\Module\Event;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Core;

use Exception;

use R3m\Io\Exception\ObjectException;

/**
 * @throws ObjectException
 * @throws Exception
 */
function function_server_url_add(Parse $parse, Data $data, stdClass $node){
    $object = $parse->object();
    $write = 0;
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
        $exception = new Exception('Only root and www-data can configure server url add...');
        Event::trigger($object, 'cli.configure.server.url.add', [
            'node' => $node,
            'bytes' => $write,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config.json';
    $read = $object->data_read($url);
    if(empty($read)){
        $read = new Data();
    }
    $read->data('server.url.' . $node->name . '.' . $node->environment, $node->url);
    try {
        $write = File::write($url, Core::object($read->data(), Core::OBJECT_JSON));
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 666 ' . $url);
        } else {
            exec('chmod 640 ' . $url);
        }
        if(empty($id)){
            exec('chown www-data:www-data ' . $url);
        }
        Event::trigger($object, 'cli.configure.server.url.add', [
            'node' => $node,
            'bytes' => $write,
        ]);
    } catch (Exception | ObjectException $exception){
        Event::trigger($object, 'cli.configure.server.url.add', [
            'node' => $node,
            'bytes' => $write,
            'exception' => $exception
        ]);
        echo $exception;
    }
    return 'Bytes written: ' . $write . "\n";
}