<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Config;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

function function_binary_create(Parse $parse, Data $data, $name=null){    
    if(empty($name)){
        $name = \R3m\Io\Cli\Bin\Controller\Bin::DEFAULT_NAME;
    }
    $object = $parse->object();
    $config = $object->data(App::CONFIG);
    $execute = $config->data(Config::DATA_PROJECT_DIR_BINARY) . \R3m\Io\Cli\Bin\Controller\Bin::EXE;    
    \R3m\Io\Module\Dir::create($config->data(Config::DATA_PROJECT_DIR_BINARY), Dir::CHMOD);
    $dir = Dir::name(\R3m\Io\Cli\Bin\Controller\Bin::DIR) . $config->data(Config::DICTIONARY . '.' . Config::DATA) . $config->data('ds');
    $source = $dir . \R3m\Io\Cli\Bin\Controller\Bin::EXE;      
    if(File::exist($execute)){
        File::delete($execute);
    }    
    File::copy($source, $execute);
    $url = \R3m\Io\Cli\Bin\Controller\Bin::TARGET . $name;
    $content = [];
    $content[] = '#!/bin/sh';
    $content[] = 'php ' . $execute . ' "$@"';
    $content = implode(PHP_EOL, $content);
    File::write($url, $content);
    shell_exec('chmod +x ' . $url);
}
