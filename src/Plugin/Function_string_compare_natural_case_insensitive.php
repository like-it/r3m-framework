<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-14
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_string_compare_natural_case_insensitive(Parse $parse, Data $data, $string1='', $string2=''){
    $result = strnatcasecmp($string1, $string2);
    return $result;
}
