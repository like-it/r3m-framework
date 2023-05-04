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

function validate_in_array(App $object, $in='', $field='', $array=''): bool
{
    if(empty($in)){
        return false;
    }
    return in_array($in, $array, true);
}
