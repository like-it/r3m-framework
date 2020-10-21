<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-30
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_string_sha1(Parse $parse, Data $data, $string='', $raw_output=false){
    $result = str_sha1($string, $raw_output);
    return $result;
}
