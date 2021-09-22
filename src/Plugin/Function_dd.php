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

function function_dd(Parse $parse, Data $data, $debug=null){
    if(
        $debug !== true &&
        in_array(
            $debug, 
            [
                '$this',
                '{$this}'
            ]
        )
    ){
        $debug = $data->data();
    }
    $trace = debug_backtrace(true);    
    if(!defined('IS_CLI')){
        echo '<pre class="priya-debug">';
    }
    echo $trace[0]['file'] . ':' . $trace[0]['line'] . PHP_EOL;    
    var_dump($debug);
    if(!defined('IS_CLI')){
        echo '</pre>';
    }
    exit;    
}
