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

function function_string_replace_case_insensitive(Parse $parse, Data $data, $search='', $replace='', $subject='', $attribute=null){
    if(!empty($attribute)){
        if(substr($attribute, 0, 1) == '$'){
            $attribute = substr($attribute, 1);
        }
        $count = 0;
        $subject = str_ireplace($search, $replace, $subject, $count);
        $data->data($attribute, $count);
    } else {
        $subject = str_ireplace($search, $replace, $subject);
    }
    $result = $subject;
    return $result;
}
