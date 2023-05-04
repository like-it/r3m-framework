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

function validate_is_email(App $object, $string='', $field='', $argument=''): bool
{
    if(filter_var($string, FILTER_VALIDATE_EMAIL)) {
        // valid address
        if($argument === false){
            return false;
        } else {
            return true;
        }
    }
    else {
        // invalid address
        if($argument === false){
            return true;
        } else {
            return false;
        }
    }
}
