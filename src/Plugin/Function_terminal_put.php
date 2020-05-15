<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_terminal_put(Parse $parse, Data $data, $command, $argument=null){
    $result = '';
    $result .= \R3m\Io\Module\Cli::tput($command, $argument);
    return $result;
}

