<?php
/**
 * @author          Remco van der Velde
 * @since           2021-03-05
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_logger_notice(Parse $parse, Data $data, $message=null, $context=[], $name=App::LOGGER_NAME){
    $object = $parse->object();
    $object->logger($name)->notice($message, $context);
}
