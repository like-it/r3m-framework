<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_cache_clear(Parse $parse, Data $data){

    $attribute = func_get_args();

    array_shift($attribute);
    array_shift($attribute);

    $object = $parse->object();

     $parse = new Parse($object);
     $command = \R3m\Io\Cli\Cache\Controller\Cache::CLEAR_COMMAND;
     foreach($command as $record){
        $execute = $parse->compile($record);
        echo 'Executing: ' . $execute . "...\n";
        $output = [];
        \R3m\Io\Module\Core::execute($execute, $output);
        $output[] = '';
        echo implode("\n", $output);
        ob_flush();
     }
}
