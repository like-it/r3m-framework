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

function function_block_json(Parse $parse, Data $data, $name='', $value=null){
    if($value === null){
        $value = $name;
        $name = null;
    }
    if(is_array($value) || is_object($value)){
        $value = Core::object($value, Core::OBJECT_JSON);
    }
    $value = trim($value, "\r\n\s\t");
    if(empty($name)){
        echo $value;
    } else {
        $data->data($name, $value);     
    }    
    return '';
}
