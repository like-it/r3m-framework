<?php

use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use Exception;
use R3m\Io\Exception\FileMoveException;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

function function_environment_toggle(Parse $parse, Data $data, $public_html=''){
    $object = $parse->object();
    $url = $object->config('project.dir.data') . 'Config.json';
    $read = $object->data_read($url);
    if(empty($read)){
        $read = new Data();
    }
    $is_development = $read->data('framework.environment');
    if($is_development == Config::MODE_DEVELOPMENT){
        $read->data('framework.environment', Config::MODE_PRODUCTION);
        $status = Config::MODE_PRODUCTION;
    } else {
        $read->data('framework.environment', Config::MODE_DEVELOPMENT);
        $status = Config::MODE_DEVELOPMENT;
    }
    try {
        File::write($url, Core::object($read->data(), Core::OBJECT_JSON));
    } catch (Exception | FileWriteException | ObjectException $exception){
        return $exception->getMessage() . "\n";
    }
    if($status == Config::MODE_PRODUCTION){
        return 'Production mode enabled.' . "\n";
    } else {
        return 'Development mode enabled.' . "\n";
    }
}

