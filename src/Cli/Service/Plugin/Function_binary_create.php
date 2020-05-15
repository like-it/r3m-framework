<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_binary_create(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    $name = array_shift($attribute);
    if(empty($name)){
        $name = \R3m\Io\Cli\Bin\Controller\Bin::DEFAULT_NAME;
    }
    $object = $parse->object();
    $config = $object->data(\R3m\Io\App::CONFIG);
    $execute = $config->data(\R3m\Io\Config::DATA_PROJECT_DIR_BINARY) . \R3m\Io\Cli\Bin\Controller\Bin::EXE;
    \R3m\Io\Module\Dir::create($config->data(\R3m\Io\Config::DATA_PROJECT_DIR_BINARY), \R3m\Io\Module\Dir::CHMOD);
    $dir = \R3m\Io\Module\Dir::name(\R3m\Io\Cli\Bin\Controller\Bin::DIR) . $config->data(\R3m\Io\Config::DICTIONARY . '.' . \R3m\Io\Config::DATA) . $config->data('ds');
    $source = $dir . \R3m\Io\Cli\Bin\Controller\Bin::EXE;
    if(\R3m\Io\Module\File::exist($execute)){
        \R3m\Io\Module\File::delete($execute);
    }
    \R3m\Io\Module\File::copy($source, $execute);
    $url = \R3m\Io\Cli\Bin\Controller\Bin::TARGET . $name;
    $content = [];
    $content[] = '#!/bin/sh';
    $content[] = 'php ' . $execute . ' "$@"';
    $content = implode(PHP_EOL, $content);
    \R3m\Io\Module\File::write($url, $content);
    shell_exec('chmod +x ' . $url);
}
