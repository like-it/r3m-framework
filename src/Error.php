<?php
/**
 * @author          Remco van der Velde
 * @since           10-02-2021
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
function exceptions_error_handler($severity, $message, $filename, $lineNumber) {
    if(
        in_array(
            $severity,
            [
                E_DEPRECATED,
                E_USER_DEPRECATED
            ]
        )
    ){
        return;
    }
    throw new ErrorException($message, 0, $severity, $filename, $lineNumber);
}

function exception_handler($exception) {
    $is_cli = false;
    if(defined('IS_CLI')){
       $is_cli = true;
    }
    $class = basename(str_replace('\\','/', get_class($exception)));
    if($is_cli){
        echo $class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine() . "\n";
        echo 'Trace: ' . "\n";
        $trace = $exception->getTrace();
        if(
            !empty($trace) &&
            is_array($trace)
        ){
            foreach($trace as $record){
                if(empty($record['class'])){
                    echo ' ' . $record['function'];
                    continue;
                }
                if(empty($record['file'])){
                    echo  ' ' . $record['class'] . $record['type'] . $record['function'];
                    continue;
                }
                echo '  ' . $record['class'] . $record['type'] . $record['function'] . ' in ' .  $record['file'] . ' on line ' . $record['line'] . "\n";
            }
        }
    } else {
        echo $class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine() . '<br>';
        echo 'Trace: ' . '<br>';
        $trace = $exception->getTrace();
        if(
            !empty($trace) &&
            is_array($trace)
        ){
            foreach($trace as $record){
                if(empty($record['class'])){
                    echo ' ' . $record['function'];
                    continue;
                }
                if(empty($record['file'])){
                    echo  ' ' . $record['class'] . $record['type'] . $record['function'];
                    continue;
                }
                echo  ' ' . $record['class'] . $record['type'] . $record['function'] . ' in ' .  $record['file'] . ' on line ' . $record['line'] . '<br>';
            }
        }
    }
}
set_error_handler('exceptions_error_handler');
set_exception_handler('exception_handler');