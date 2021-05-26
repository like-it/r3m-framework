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

function function_string_compare_version(Parse $parse, Data $data, $version1='', $version2='', $operator=null){
    if($operator === null){
        $result = version_compare($version1, $version2);
    } else {
        $operator = strtolower($operator);
        $result = version_compare($version1, $version2, $operator);
    }
    return $result;
}
