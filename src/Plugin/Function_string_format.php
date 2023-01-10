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

function function_string_format(Parse $parse, Data $data, $string='', $format=''){
    $attribute = func_get_args();
    array_shift($attribute);
    array_shift($attribute);
    array_shift($attribute);
    array_shift($attribute);
    $scan = sscanf($string, $format);
    if(count($attribute) > 0){
        $result = new stdClass();
        foreach($attribute as $key){
            $result->{$key} = array_shift($scan);
        }
    } else {
        $result = $scan;
    }
    return $result;
}
