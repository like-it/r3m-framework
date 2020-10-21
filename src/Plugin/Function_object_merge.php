<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-18
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Core;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_object_merge(Parse $parse, Data $data, $object=''){
    $attribute = func_get_args();
    array_shift($attribute);
    array_shift($attribute);
    array_shift($attribute);
    foreach($attribute as $merge){
        $object = Core::object_merge($object, $merge);
    }
    $result = $object;
    return $result;
}
