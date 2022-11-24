<?php
/**
 * @author          Remco van der Velde
 * @since           2022-11-24
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_route_current(Parse $parse, Data $data){
    $object = $parse->object();
    return $object->route()->current();//find($object, $name, $options);
}
