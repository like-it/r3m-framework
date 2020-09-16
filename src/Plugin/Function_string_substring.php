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

function function_string_substring_replace(Parse $parse, Data $data, $string='', $replacement='', $start=0, $length=null){
    if(empty($length)){
        $result = substr_replace($string, $replacement, $start);
    } else {
        $result = substr_replace($string, $replacement, $start, $length);
    }
    return $result;
}
