<?php
/**
 * @author          Remco van der Velde
 * @since           2021-03-05
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_logger_info(Parse $parse, Data $data, $message=null, $context=[], $name=''){
    $object = $parse->object();
    if(empty($name)){
        $name = $object->config('project.log.name');
    }
    $object->logger($name)->info($message, $context);
}