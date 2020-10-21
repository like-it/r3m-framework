<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-24
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_preg_quote(Parse $parse, Data $data, $string='', $delimiter=null){
    if($delimiter !== null){
        $result = preg_quote($string, $delimiter);
    } else {
        $result = preg_quote($string);
    }
    return $result;
}
