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

function function_config(Parse $parse, Data $data, $attribute=null, $value=null){
    $object = $parse->object();
    if($value !== null){
        $object->config($attribute, $value);
    } else {
        return $object->config($attribute);
    }
}
