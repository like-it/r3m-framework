<?php

use R3m\Io\Config;

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Event;

use Exception;
use R3m\Io\Exception\FileAppendException;

/**
 * @throws FileAppendException
 * @throws Exception
 */
function function_host_add(Parse $parse, Data $data, $ip='', $host=''){
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
        $exception = new Exception('Only root can configure host add...');
        Event::trigger($object, 'configure.host.add', [
            'ip' => $ip,
            'host' => $host,
            'exception' => $exception
        ]);
        throw $exception;
    }
    if(empty($ip)){
        $ip = '0.0.0.0';
    }
    if(empty($host)){
        $exception = new Exception('Host cannot be empty...');
        Event::trigger($object, 'configure.host.add', [
            'ip' => $ip,
            'host' => $host,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $response = null;
    $url = '/etc/hosts';
    if(File::exist($url)){
        $data = explode("\n", File::read($url));
        foreach($data as $nr => $row){
            if(stristr($row, $host) !== false){
                Event::trigger($object, 'host.add', [
                    'ip' => $ip,
                    'host' => $host,
                ]);
                return $response;
            }
        }
        $data = $ip . "\t" . $host . "\n";
        $append = File::append($url, $data);
        $response = 'ip: ' . $ip  .' host: ' . $host . ' added.' . "\n";
        Event::trigger($object, 'configure.host.add', [
            'ip' => $ip,
            'host' => $host,
            'exist' => false
        ]);
    }
    return $response;
}

