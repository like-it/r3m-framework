<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-16
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_string_trim_left(Parse $parse, Data $data, $string='', $mask=null){
    if($mask === null){
        $mask = " \t\n\r\0\x0B";
    }
    $result = ltrim($string, $mask);
    return $result;
}
