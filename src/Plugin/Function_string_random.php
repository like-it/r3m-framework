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

function function_string_random(Parse $parse, Data $data, $length=1){
    $length += 0;
    $result = '';
    for($i=0; $i < $length; $i++){
        $char = rand(32, 126);
        $char = chr($char);
        $result .= $char;
    }
    return $result;
}
