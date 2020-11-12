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
    ob_start();
    if(!defined('IS_CLI')){
        echo '<pre class="priya-debug">';
    }
    echo $trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL;
    ob_flush();
    var_dump($data);
    $data = ob_get_contents();
    ob_end_clean();
    $temp = explode(':', $data, 3);
    $temp[0] = '';
    $temp[1] = '';
    $data = implode('', $temp);
    $explode = explode(PHP_EOL, $data);
    $shift = array_shift($explode);
    if(defined('IS_CLI')){
        echo $shift . PHP_EOL . PHP_EOL;
        echo implode(PHP_EOL, $explode);
    } else {
        echo $shift . '<br><br>' . PHP_EOL . PHP_EOL;
        echo implode('<br>' . PHP_EOL, $explode);
        echo '</pre>';
    }
}

function dd($data=null){
    $trace = debug_backtrace(true);
    ob_start();
    if(!defined('IS_CLI')){
        echo '<pre class="priya-debug">';
    }
    echo $trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL;
    ob_flush();
    var_dump($data);
    $data = ob_get_contents();
    ob_end_clean();
    $temp = explode(':', $data, 3);
    $temp[0] = '';
    $temp[1] = '';
    $data = implode('', $temp);
    $explode = explode(PHP_EOL, $data);
    $shift = array_shift($explode);
    if(defined('IS_CLI')){
        echo $shift . PHP_EOL . PHP_EOL;
        echo implode(PHP_EOL, $explode);
    } else {
        echo $shift . '<br><br>' . PHP_EOL . PHP_EOL;
        echo implode('<br>' . PHP_EOL, $explode);
        echo '</pre>';
    }
    exit;
}