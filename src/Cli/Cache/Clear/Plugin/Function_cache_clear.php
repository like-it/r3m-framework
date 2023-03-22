<?php

use R3m\Io\Config;

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;

function function_cache_clear(Parse $parse, Data $data){
    $object = $parse->object();
    $dir = new Dir();
    $temp_dir = $object->config('framework.dir.temp');
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
        $id = $object->config(Config::POSIX_ID);
        foreach($read as $file){
            if($file->type === Dir::TYPE){
                $file->number = false;
                if(is_numeric($file->name)){
                    $file->number = $file->name + 0;
                }
                if(
                    $file->number !== false &&
                    file_exists($file->url) &&
                    empty($id)
                ){
                    Dir::remove($file->url);
                    echo 'Removed: ' . $file->url . PHP_EOL;
                }
                elseif(
                    $file->number !== false &&
                    file_exists($file->url) &&
                    $id === $file->number
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
