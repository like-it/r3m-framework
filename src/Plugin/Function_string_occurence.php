<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-15
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_string_occurence(Parse $parse, Data $data, $haystack='', $needle='', $before_needle=false){
    $result = strstr($haystack, $needle, $before_needle);
    return $result;
}
