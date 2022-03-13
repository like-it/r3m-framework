<?php
/**
 * @author          Remco van der Velde
 * @since           2021-03-05
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Core;

function function_logger_warning(Parse $parse, Data $data, $message=null, $context=[]){
    $object = $parse->object();
    $object->logger()->warning($message, $context);
}
