<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function modifier_replace_case_insensitive(Parse $parse, Data $data, $value, $search='', $replace=''){
    if(is_array($value)){
        foreach($value as $key => $record){
            $value[$key] = str_ireplace($search, $replace, $record);
        }
        return $value;
    } else {
        return str_ireplace($search, $replace, $value);
    }
}
