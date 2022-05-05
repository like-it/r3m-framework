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

function function_trait(Parse $parse, Data $data, $name='', $value=null){
    $explode = explode(':', $name);
    if(array_key_exists(1, $explode)){
        $namespace = $explode[0];
        $name = $explode[1];
    } else {
        $namespace = '';
        $name = $explode[0];
    }
    if($namespace){
        $data->set('trait.' . $namespace . '.' . $name, $value);
    } else {
        $data->set('trait.' . $name, $value);
    }
}
