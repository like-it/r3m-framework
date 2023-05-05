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
use R3m\Io\Module\Parse\Token;

/**
 * @throws Exception
 */
function validate_string_has_dot(App $object, $string='', $field='', $options=''): bool
{
    $explode = explode('.', $string, 2);
    if(count($explode) == 2){
        if($options === 'inverse'){
            return false;
        }
        return true;
    }
    if($options === 'inverse'){
        return true;
    }
    return false;
}
