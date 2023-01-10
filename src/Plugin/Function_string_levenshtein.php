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

function function_string_levenhstein(Parse $parse, Data $data, $string1='', $string2='', $cost_insert=null, $cost_replace=null, $cost_delete=null){
    if($cost_insert===null){
        $result = levenshtein($string1, $string2);
    } else {
        $result = levenshtein($string1, $string2, $cost_insert, $cost_replace, $cost_delete);
    }
    return $result;
}
