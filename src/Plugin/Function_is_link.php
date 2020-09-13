<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_is_link(Parse $parse, Data $data){
    $attribute = func_get_args();
    array_shift($attribute);
    array_shift($attribute);
    $link = array_shift($attribute);
    return is_link($link);
}
