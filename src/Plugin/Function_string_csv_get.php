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

function function_string_csv_get(Parse $parse, Data $data, $input='', $delimiter=',', $enclosure='"', $escape='\\'){
    $result = str_getcsv($input, $delimiter, $enclosure, $escape);
    return $result;
}
