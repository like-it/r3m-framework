<?php
/**
 * @author          Remco van der Velde
 * @since           2021-03-05
 */
use R3m\Io\App;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_logger_alert(Parse $parse, Data $data, $message=null, $context=[], $name=''){
    $object = $parse->object();
    if(empty($name)){
        $name = $object->config('project.log.name');
    }
    $object->logger($name)->alert($message, $context);
}