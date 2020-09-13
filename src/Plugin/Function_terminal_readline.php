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

function function_terminal_readline(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    $text = array_shift($attribute);

    echo $text;
    if(ob_get_length() > 0){
        ob_flush();
    }
    $input = trim(fgets(STDIN));
    return $input;
}
