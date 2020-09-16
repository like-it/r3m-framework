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
use R3m\Io\Module\Core;

function function_string_translate(Parse $parse, Data $data, $string='', $from='', $to=''){
    if(is_string($from)){
        $result = strtr($string, $from, $to);
    } else {
        $from = Core::object($from, 'array');
        $result = strtr($string, $from);
    }
    return $result;
}
