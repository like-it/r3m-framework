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

function function_string_crypt(Parse $parse, Data $data, $string='', $salt=null){
    if(!empty($salt)){
        $result = crypt($string, $salt);
    } else {
        $result = '';
    }
    return $result;
}
