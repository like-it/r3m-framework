<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;
use R3m\Io\App;

function function_ln(Parse $parse, Data $data){
    $object = $parse->object();

    $source = App::parameter($object, 'ln', 1);
    $target = App::parameter($object, 'ln', 2);

    if(File::exist($target)){
        return;
    }
    Core::execute('ln -s ' . $source . ' ' . $target);
}
