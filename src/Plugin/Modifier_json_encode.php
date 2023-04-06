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

function modifier_json_encode(Parse $parse, Data $data, $value, $flags=0, $depth=512){
    if(is_string($flags)){
        $flags = constant($flags);
    }
    elseif(is_numeric($flags)){
        $flags += 0;
    }
    return json_encode($value, $flags, $depth);
}
