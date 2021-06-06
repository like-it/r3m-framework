<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-15
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_string_position_last_occurence(Parse $parse, Data $data, $haystack='', $needle='', $offset=0){
    $result = strrpos($haystack, $needle, $offset);
    return $result;
}
