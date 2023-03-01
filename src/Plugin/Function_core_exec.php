<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use stdClass;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;


function function_core_exec(Parse $parse, Data $data, $command, $attribute=null, $type=null){
    $object = $parse->object();
    if($object->config('project.log.deprecated')){
        $object->logger($object->config('project.log.deprecated'))->notice('Deprecated, plugin: core_exec, use core_execute');
    }
    elseif($object->config('project.log.name')){
        $object->logger($object->config('project.log.name'))->notice('Deprecated, plugin: core_exec, use core_execute');
    }

    $output = [];
    Core::execute($object, $command, $output, $error, $type);
    if($attribute) {
        if (substr($attribute, 0, 1) === '$') {
            $attribute = substr($attribute, 1);
        }
        if($error){
            $data->data($attribute . '_error', $error);
        }
        $data->data($attribute, $output);
    }
}
