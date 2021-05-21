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
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_parameter(Parse $parse, Data $data, $name=null, $offset=null){
    $object = $parse->object();
    return $object->parameter($object, $name, $offset);
}
