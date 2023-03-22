<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;
use R3m\Io\Module\Route;

function function_cache_clear(Parse $parse, Data $data){   
    $object = $parse->object();
    $temp_dir = $object->config('framework.dir.temp');
    $dir = new Dir();
    $read = $dir->read($temp_dir, true);
    $parse = new Parse($object);
    if($object->config('ramdisk.url')){
        $command = \R3m\Io\Cli\Cache\Controller\Cache::RAMDISK_CLEAR_COMMAND;
        $execute = $parse->compile($command);
        echo 'Executing: ' . $execute . "...\n";
        Core::execute($object, $execute, $output);
        echo $output . PHP_EOL;
        ob_flush();
    }
    if($read){
        foreach($read as $file){
            if($file->type === Dir::TYPE){
                if(
                    is_numeric($file->name) &&
                    file_exists($file->url)
                ){
                    Dir::remove($file->url);
                    echo 'Removed: ' . $file->url . PHP_EOL;
                }
            }
        }
    }
    if(File::exist($object->config('project.dir.vendor') . 'Doctrine')){
        $cacheDriver = new \Doctrine\Common\Cache\ArrayCache();
        $cacheDriver->deleteAll();
    }
    opcache_reset();
}
