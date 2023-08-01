<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Dir;

function function_parse_restart(Parse $parse, Data $data){
    $object = $parse->object();
    $object->config('ramdisk.is.disabled', true);
    $temp_dir = $object->config('framework.dir.temp');
    $dir = new Dir();
    $read = $dir->read($temp_dir, true);
    if($read){
        foreach($read as $file){
            if($file->type === Dir::TYPE){
                if(
                    stristr($file->url, $object->config('dictionary.compile')) !== false &&
                    file_exists($file->url)
                ){
                    Dir::remove($file->url);
                    echo 'Removed: ' . $file->url . PHP_EOL;
                }
            }
        }
    }
}
