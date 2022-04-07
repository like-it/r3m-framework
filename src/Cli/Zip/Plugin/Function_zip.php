<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;
use R3m\Io\App;

function function_zip(Parse $parse, Data $data){
    $object = $parse->object();
    $object->logger()->error('test2: zip');
    $archive = App::parameter($object, 'zip', 1);
    dd($object->request());
    if($archive === 'archive'){
        $source = App::parameter($object, 'archive', 1);
        $target = App::parameter($object, 'archive', 2);
        d($source);
        dd($target);
    }




    /*
    if(File::exist($target)){
        return;
    }
    Core::execute('ln -s ' . $source . ' ' . $target);
    */
}
