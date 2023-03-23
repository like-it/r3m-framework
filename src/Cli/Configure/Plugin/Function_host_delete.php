<?php

use R3m\Io\Config;

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Event;


/**
 * @throws Exception
 */
function function_host_delete(Parse $parse, Data $data, $host=''){
    $object = $parse->object();
    $id = $object->config(Config::POSIX_ID);
    if(
        !in_array(
            $id,
            [
                0
            ],
            true
        )
    ){
        $exception = new Exception('Only root can configure host delete...');
        Event::trigger($object, 'configure.host.delete', [
            'host' => $host,
            'exception' => $exception
        ]);
        throw $exception;
    }
    if(empty($host)){
        $exception = new Exception('Host cannot be empty...');
        Event::trigger($object, 'configure.host.delete', [
            'host' => $host,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $url = '/etc/hosts';
    $data = explode("\n", File::read($url));
    foreach($data as $nr => $row){
        if(stristr($row, $host) !== false){
            unset($data[$nr]);
        }
    }
    $data = implode("\n", $data);
    File::write($url, $data);
    Event::trigger($object, 'configure.host.delete', [
        'host' => $host,
    ]);
}

