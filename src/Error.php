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
    throw new ErrorException($message, 0, $severity, $filename, $lineNumber);
}

function exception_handler($exception) {
    $class = basename(str_replace('\\','/', get_class($exception)));
    echo $class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine() . "\n";
    echo 'Trace: ' . "\n";
    $trace = $exception->getTrace();
    if(
        !empty($trace) &&
        is_array($trace)
    ){
        foreach($trace as $record){
            echo "  " . $record['class'] . $record['type'] . $record['function'] . ' in ' .  $record['file'] . ' on line ' . $record['line'] . "\n";
        }
    }
}
set_error_handler('exceptions_error_handler');
set_exception_handler('exception_handler');