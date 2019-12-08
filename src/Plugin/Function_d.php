<?php

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
    $debug = ob_get_contents();
    ob_end_clean();
    $explode = explode(PHP_EOL, $debug);
    array_shift($explode);
    if(defined('IS_CLI')){
        echo implode(PHP_EOL, $explode);
    } else {
        echo implode('<br>' . PHP_EOL, $explode);
        echo '</pre>';
    }
}

