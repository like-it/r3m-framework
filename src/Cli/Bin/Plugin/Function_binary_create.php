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
    $execute = $object->config(Config::DATA_PROJECT_DIR_BINARY) . \R3m\Io\Cli\Bin\Controller\Bin::EXE;
    Dir::create($object->config(Config::DATA_PROJECT_DIR_BINARY), Dir::CHMOD);
    $dir = Dir::name(\R3m\Io\Cli\Bin\Controller\Bin::DIR) .
        $object->config(
            Config::DICTIONARY .
            '.' .
            Config::DATA
        ) .
        $object->config('ds');
    $source = $dir . \R3m\Io\Cli\Bin\Controller\Bin::EXE;      
    if(File::exist($execute)){
        File::delete($execute);
    }    
    File::copy($source, $execute);
    $url = \R3m\Io\Cli\Bin\Controller\Bin::TARGET . $name;
    $content = [];
    $content[] = '#!/bin/sh';
    # added $name as this was a bug in updating the cms
    $content[] = '_=' . $name . ' php ' . $execute . ' "$@"';
    $content = implode(PHP_EOL, $content);
    File::write($url, $content);
    shell_exec('chmod +x ' . $url);
}
