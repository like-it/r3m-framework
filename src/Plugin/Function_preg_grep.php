<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-24
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_preg_grep(Parse $parse, Data $data, $pattern=null, $input=[], $flags=0){
    if(is_string($flags)){
        $flags = constant($flags);
    }
    if($flags != 0){
        $result = preg_filter($pattern, $input, $flags);
    } else {
        $result = preg_filter($pattern, $input);
    }
    return $result;
}
