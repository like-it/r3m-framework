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

function function_d(Parse $parse, Data $data, $debug=null){
    $trace = debug_backtrace(true);
    ob_start();
    if(!defined('IS_CLI')){
        echo '<pre class="priya-debug">';
    }
    echo $trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL;
    ob_flush();
    var_dump($debug);
    if(!defined('IS_CLI')){
        echo '</pre>';    
    }
}
