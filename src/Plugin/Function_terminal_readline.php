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
use R3m\Io\Module\Cli;

use R3m\Io\Exception\ObjectException;

/**
 * @throws ObjectException
 */
function function_terminal_readline(Parse $parse, Data $data, $text='', $type=null){
    if(
        $text === 'stream' &&
        $type === null
    ){
        return Cli::read($text);
    }
    return Cli::read($type, $text);
}
