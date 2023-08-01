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
use R3m\Io\App;

function validate_string_equals(App $object, $string='', $field='', $argument=''): bool
{
    if($string === $argument){
        return true;
    }
    return false;
}
