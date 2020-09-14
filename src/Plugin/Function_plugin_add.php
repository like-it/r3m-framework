<?php
/**
 * not working
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_plugin_add(Parse $parse, Data $data, $directory=null){
    $config = $parse->object()->data(\R3m\Io\App::CONFIG);
    $plugin = $config->data('parse.dir.plugin');

    $plugin[] = $directory;
    $config->data('parse.dir.plugin', $plugin);
}
