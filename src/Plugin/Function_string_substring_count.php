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

function function_string_substring_count(Parse $parse, Data $data, $haystack='', $needle='', $offset=0, $length=null){
    if($length === null){
        $result = substr_count($haystack, $needle, $offset);
    } else {
        $result = substr_count($haystack, $needle, $offset, $length);
    }
    return $result;
}
