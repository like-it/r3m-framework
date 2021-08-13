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

function function_password_rehash_need(Parse $parse, Data $data, $hash='', $algorithm='PASSWORD_DEFAULT', $options=null){
    if(is_string($algorithm)){
        $algorithm = constant($algorithm);
    }
    if(!is_array($options)){
        $result = password_needs_rehash($hash, $algorithm);
    } else {
        $result = password_needs_rehash($hash, $algorithm, $options);
    }
    return $result;
}
