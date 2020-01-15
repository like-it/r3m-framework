<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_plugin_add(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    $config = $parse->object()->data(\R3m\Io\App::CONFIG);
    $plugin = $config->data('parse.dir.plugin');

    $plugin[] = array_shift($attribute);
    $config->data('parse.dir.plugin', $plugin);
//     $parse->storage()->data('plugin', $plugin);

}
