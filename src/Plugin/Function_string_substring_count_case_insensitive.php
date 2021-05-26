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

function function_string_substring_count_case_insensitive(Parse $parse, Data $data, $haystack='', $needle='', $offset=0, $length=null){
    $haystack = strtolower($haystack);
    $needle = strtolwer($needle);
    if($length === null){
        $result = substr_count($haystack, $needle, $offset);
    } else {
        $result = substr_count($haystack, $needle, $offset, $length);
    }
    return $result;
}
