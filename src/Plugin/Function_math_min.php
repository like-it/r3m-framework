<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-20
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_math_min(Parse $parse, Data $data, ...$array){
    if(empty($array)){
        return;
    }
    return min(...$array);
}
