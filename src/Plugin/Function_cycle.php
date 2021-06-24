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

function function_cycle(Parse $parse, Data $data, $name, $arguments=[]){
    $name = 'r3m.io.cycle.' . $name;
    $array = $parse->object()->data($name);
    if(
        $array &&
        is_array($array)
    ){
        $next = next($array);
        if($next === false){
            $next = reset($array);
        }
        $parse->object()->data($name, $array);
        return $next;
    } else {
        $parse->object()->data($name, $arguments);
        return reset($arguments);
    }
}
