<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;


/**
 * @throws \R3m\Io\Exception\FileWriteException
 * @throws Exception
 */
function function_host_delete(Parse $parse, Data $data){
    $id = posix_geteuid();
    if(
        !in_array(
            $id,
            [
                0
            ]
        )
    ){
        throw new Exception('Only root can configure host delete...');
    }
    $attribute = func_get_args();
    array_shift($attribute);
    array_shift($attribute);
    $host = array_shift($attribute);
    if(empty($host)){
        throw new Exception('Host cannot be empty...');
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
}

