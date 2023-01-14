<?php
/**
 * @author          Remco van der Velde
 * @since           2021-03-05
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_logger_emergency(Parse $parse, Data $data, $message=null, $context=[], $name=''){
    $object = $parse->object();
    if(empty($name)){
        $name = $object->config('logger.default.name');
    }
    $object->logger($name)->emergency($message, $context);
}
