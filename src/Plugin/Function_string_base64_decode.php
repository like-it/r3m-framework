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

function function_string_base64_decode(Parse $parse, Data $data, $string=null, $strict=null){
    if(empty($strict)){
        $result = base64_decode($string);
    } else {
        $result = base64_decode($string, true);
    }
    return $result;
}
