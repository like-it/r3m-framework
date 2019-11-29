<?php

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
    $explode = explode(PHP_EOL, $data);
    array_shift($explode);
    if(defined('IS_CLI')){
        echo implode(PHP_EOL, $explode);
    } else {
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
    $explode = explode(PHP_EOL, $data);
    array_shift($explode);
    if(defined('IS_CLI')){
        echo implode(PHP_EOL, $explode);
    } else {
        echo implode('<br>' . PHP_EOL, $explode);
        echo '</pre>';
    }
    exit;
}