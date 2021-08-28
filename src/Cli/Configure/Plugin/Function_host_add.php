<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;


function function_host_add(Parse $parse, Data $data, $ip='', $host=''){
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

