<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

use Exception;
use R3m\Io\Exception\FileAppendException;

/**
 * @throws FileAppendException
 * @throws Exception
 */
function function_host_add(Parse $parse, Data $data, $ip='', $host=''){
    $id = posix_geteuid();
    if(
        !in_array(
            $id,
            [
                0
            ]
        )
    ){
        throw new Exception('Only root can configure host add...');
    }
    if(empty($ip)){
        $ip = '0.0.0.0';
    }
    if(empty($host)){
        throw new Exception('Host cannot be empty...');
    }
    $url = '/etc/hosts';
    $data = explode("\n", File::read($url));
    foreach($data as $nr => $row){
        if(stristr($row, $host) !== false){
            return;
        }
    }
    $data = $ip . "\t" . $host . "\n";
    $append = File::append($url, $data);
    return 'ip: ' . $ip  .' host: ' . $host . ' added.' . "\n";
}

