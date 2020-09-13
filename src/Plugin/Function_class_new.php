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

function function_class_new(Parse $parse, Data $data, $name){
    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);
    array_shift($attribute);

    $count = count($attribute);

    switch($count){
        case 1 :
            return new $name(array_shift($attribute));
        break;
        default :
            return new $name();
        break;
    }
}
