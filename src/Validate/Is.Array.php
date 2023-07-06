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

function validate_is_array(App $object, $array=[], $field='', $argument=''): bool
{
    d($array);
    d($field);
    d($argument);
    return is_array($array);
}
