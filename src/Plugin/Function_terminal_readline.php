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

function function_terminal_readline(Parse $parse, Data $data, $text='', $type=null){
    if($type === 'hidden'){
        echo $text;
        ob_flush();
        if(ob_get_length() > 0){
            ob_flush();
        }
        system('stty -echo');
        $input = trim(fgets(STDIN));
        system('stty echo');
        echo PHP_EOL;
        return $input;
    } else {
        echo $text;
        ob_flush();
        if(ob_get_length() > 0){
            ob_flush();
        }
        while ($line = fgets(fopen('php://stdin','r'))) {
            if(stristr($line, "\n") !== false){
                break;
            }
        }
        $input = $line;
        echo PHP_EOL;
        return $input;
    }

}
