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
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_data_delete(Parse $parse, Data $data, $attribute){
    if(substr($attribute, 0, 1) === '$'){
        $attribute = substr($attribute, 1);
    }
    $data->delete($attribute);
}
