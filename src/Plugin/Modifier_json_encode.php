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

function modifier_json_encode(Parse $parse, Data $data, $value, $options=0, $depth=512){
    if(is_string($options)){        
        $options = constant($options);        
    }
    elseif(is_numeric($options)){
        $options += 0;
    } else {
        $options = 0;
    }
    return json_encode($value, $options, $depth);
}
