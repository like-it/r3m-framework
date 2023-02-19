<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;

function function_cache_clear(Parse $parse, Data $data){
    $object = $parse->object();
    $parse = new Parse($object);
    $command = \R3m\Io\Cli\Cache\Controller\Cache::CLEAR_COMMAND;
    foreach($command as $record){
        $execute = $parse->compile($record);
        echo 'Executing: ' . $execute . "...\n";
        Core::execute($object, $execute, $output);
        if(is_array($output)){
            echo implode(PHP_EOL, $output) . PHP_EOL;
            ob_flush();
        } else{
            echo $output . PHP_EOL;
            ob_flush();
        }
    }
    if(File::exist($object->config('project.dir.vendor') . 'Doctrine')){
        $cacheDriver = new \Doctrine\Common\Cache\ArrayCache();
        $cacheDriver->deleteAll();
    }
    opcache_reset();
}
