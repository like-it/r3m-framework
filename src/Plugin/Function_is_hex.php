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

function function_is_hex(Parse $parse, Data $data){
    $attribute = func_get_args();
    array_shift($attribute);
    array_shift($attribute);
    $hex = array_shift($attribute);
    if(strtolower($hex) == 'nan'){
        $hex = NAN;
    }
    return ctype_xdigit($hex);
}
