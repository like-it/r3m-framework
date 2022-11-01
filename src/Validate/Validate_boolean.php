<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-18
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse\Token;

function validate_boolean(R3m\Io\App $object, $string='', $field='', $argument=''){
    $bool = $string;
    if(
        $bool == '1' ||
        $bool == 'true' ||
        $bool === true
    ){
        $bool = true;
    } else {
        $bool = false;
    }
    if(
        $argument == '1' ||
        $argument == 'true' ||
        $argument === true
    ){
        $argument = true;
    } else {
        $argument = false;
    }
    return $bool === $argument;
}
