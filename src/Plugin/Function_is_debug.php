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

function function_is_debug(Parse $parse, Data $data){
    $attribute = func_get_args();
    array_shift($attribute);
    array_shift($attribute);
    $is_debug = array_shift($attribute);
    if($is_debug === null){
        return $parse->object()->data('is.debug');
    } else {
        return $parse->object()->data('is.debug', $is_debug);
    }
}
