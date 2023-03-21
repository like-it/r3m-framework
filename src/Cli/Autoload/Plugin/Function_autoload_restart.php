<?php

use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

function function_autoload_restart(Parse $parse, Data $data){
    $object = $parse->object();

    $temp_dir = $object->config('framework.dir.temp');
    $dir = new Dir();
    $read = $dir->read($temp_dir, true);
    if($read){
        foreach($read as $file){
            if($file->type === Dir::TYPE){
                if(stristr($file, 'autoload') !== false){
                    d($file);
                }
            }
        }
    }
    /*
    $autoload = $object->data(\R3m\Io\App::AUTOLOAD_R3M);
    $cache_dir = $autoload->cache_dir();

    Dir::remove($cache_dir);
    if($object->config('autoload.cache.class')){
        Dir::remove($object->config('autoload.cache.class'));
    }
    */
}
