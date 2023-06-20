<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-14
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

/**
 * @throws \R3m\Io\Exception\ObjectException
 */
function function_execute_background(Parse $parse, Data $data, $command=''){
    $command = (string) $command;
    $command = escapeshellcmd($command);
    if(substr($command, 0, -1) !== '&'){
        $command .= '&';
    }
    d($command);
    exec($command, $output);
    return $output;
}
