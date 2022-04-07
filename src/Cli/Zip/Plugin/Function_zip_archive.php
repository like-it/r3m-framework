<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;
use R3m\Io\App;

function function_zip_archive(Parse $parse, Data $data){
    $object = $parse->object();
    $object->logger()->error('test2: zip');
    $source = App::parameter($object, 'archive', 1);
    $target = App::parameter($object, 'archive', 2);
    d($source);

    $limit = $parse->limit();
    $parse->limit([
        'function' => [
            'date'
        ]
    ]);
    try {
        $target = $parse->compile($target, [], $data);
        $parse->limit($limit);
    } catch (Exception $exception) {
        d($exception);
    }
    dd($target);
    /*
    if(File::exist($target)){
        return;
    }
    Core::execute('ln -s ' . $source . ' ' . $target);
    */
}
