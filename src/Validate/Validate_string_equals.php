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

function validate_string_equals(R3m\Io\App $object, $string='', $field='', $argument=''){
    $compare = $object->request('node.' . $argument);
    if(empty($compare)){
        $compare = $object->request($argument);
    }
    if($string === $compare){
        return true;
    }
    return false;
}
