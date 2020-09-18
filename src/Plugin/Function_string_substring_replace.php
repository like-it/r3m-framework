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

function function_string_substring(Parse $parse, Data $data, $string='', $start=0, $length=null){
    if(empty($length)){
        $result = substr($string, $start);
    } else {
        $result = substr($string, $start, $length);
    }
    return $result;
}
