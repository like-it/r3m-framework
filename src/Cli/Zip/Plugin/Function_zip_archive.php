<?php

use Host\Subdomain\Host\Extension\Service\Export;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;
use R3m\Io\App;

function function_zip_archive(Parse $parse, Data $data){
    $object = $parse->object();
    $source = App::parameter($object, 'archive', 1);
    $target = App::parameter($object, 'archive', 2);
    $limit = $parse->limit();
    $parse->limit([
        'function' => [
            'date'
        ]
    ]);
    try {
        $target = $parse->compile($target, [], $data);
        $parse->setLimit($limit);
    } catch (Exception $exception) {
        echo $exception->getMessage() . PHP_EOL;
        return;
    }
    if(
        Dir::is($source) &&
        !File::exist($target)
    ){
        $dir = new Dir();
        $read = $dir->read($source, true);
        $host = [];
        foreach($read as $file){
            $host[] = $file;
        }
        foreach($host as $nr => $file){
            if($file->type === Dir::TYPE){
                unset($host[$nr]);
            }
        }
        $dir = Dir::name($target);
        if(
            $dir &&
            !in_array(
                $dir,
                [
                    $object->config('ds')
                ],
                true
            )
        ){
            Dir::create($dir);
        }
        $zip = new \ZipArchive();
        $zip->open($target, \ZipArchive::CREATE);
        foreach($host as $file){
            if(substr($file->url, 0, 1) === $object->config('ds')){
                $location = substr($file->url, 1);
            } else {
                $location = $file->url;
            }
            $zip->addFile($file->url, $location);
        }
        $zip->close();
        echo $target;
    }
    elseif(
        File::is($source) &&
        !File::exist($target)
    ) {
        $dir = Dir::name($target);
        if(
            $dir &&
            !in_array(
                $dir,
                [
                    $object->config('ds')
                ],
                true
            )
        ){
            Dir::create($dir);
        }
        $zip = new \ZipArchive();
        $zip->open($target, \ZipArchive::CREATE);
        if(substr($source, 0, 1) === $object->config('ds')){
            $location = substr($source, 1);
        } else {
            $location = $source;
        }
        $zip->addFile($source, $location);
        $zip->close();
        echo $target;
    }
}
