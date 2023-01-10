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

function function_string_tag_strip(Parse $parse, Data $data, $string='', $allowable_tags=null){
    if($allowable_tags === null){
        $result = strip_tags($string);
    } else {
        $result = strip_tags($string, $allowable_tags);
    }
    return $result;
}
