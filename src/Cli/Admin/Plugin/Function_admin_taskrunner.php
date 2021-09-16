<?php

use R3m\Io\App;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

function function_admin_taskrunner(Parse $parse, Data $data){
    $object = $parse->object();

    $dir = new Dir();
    $read = $dir->read($object->config('project.dir.data') . 'Input' . $object->config('ds'), true);
    foreach($read as $nr => $file){
        if($file->type == File::TYPE){
            ob_start();
            $task = File::read($file->url);
            $output = [];
            Core::execute($task, $output);
            echo implode(PHP_EOL, $output);
            $content = ob_get_contents();
            ob_end_clean();
            dd($content);
        }
    }
    dd($read);


    /*
    $task  = App::parameter($object, 'task', 1);
    $euid = posix_geteuid();
    $dir = $object->config('project.dir.data') .'Input' . $object->config('ds');
    $dir_part = $euid . $object->config('ds');
    Dir::create($dir . $dir_part);
    $uuid = Core::uuid();
    $url_part = $dir_part . $uuid . '.task';
    $url = $dir . $url_part;
    File::write($url, $task);
    return $url_part . PHP_EOL;
    */


}
