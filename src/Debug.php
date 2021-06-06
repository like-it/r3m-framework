<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
function d($data=null){
    $trace = debug_backtrace(true);    
    if(!defined('IS_CLI')){
        echo '<pre class="priya-debug">';
    }
    echo $trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL;    
    var_dump($data);
    if(!defined('IS_CLI')){
        echo '</pre>';
    }
}

function dd($data=null){
    $trace = debug_backtrace(true);    
    if(!defined('IS_CLI')){
        echo '<pre class="priya-debug">';
    }
    echo $trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL;    
    var_dump($data);
    if(!defined('IS_CLI')){
        echo '</pre>';
    }
    exit;    
}