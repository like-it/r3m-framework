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

function validate_in_array(R3m\Io\App $object, $field='', $array=''){
    if($object->request('has', 'node.' . $field)){
        $in = $object->request('node.' . $field);
    }
    elseif($object->request('has', 'node_' . $field)) {
        $in = $object->request('node_' . $field);
    }
    else {
        $in = $object->request($field);
    }
    if(empty($in)){
        return false;
    }
    return in_array($in, $array);
}
