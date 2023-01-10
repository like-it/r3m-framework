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

function function_object_new(Parse $parse, Data $data){
    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);
    $name = array_shift($attribute);
    if(empty($name)){
        $name = '\stdClass';
    }
    $count = count($attribute);
    switch($count){
        case 1 :
            return new $name(array_shift($attribute));
        case 2 :
            return new $name(array_shift($attribute), array_shift($attribute));
        case 3 :
            return new $name(array_shift($attribute), array_shift($attribute), array_shift($attribute));
        case 4 :
            return new $name(array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute));
        case 5 :
            return new $name(array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute));
        case 6 :
            return new $name(array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute));
        case 7 :
            return new $name(array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute));
        case 8 :
            return new $name(array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute));
        case 9 :
            return new $name(array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute), array_shift($attribute));
        default :
            return new $name();
    }
}
