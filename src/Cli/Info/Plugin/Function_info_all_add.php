<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Cli;

function function_info_all_add(Parse $parse, Data $data, $list){
    $result = [];
    foreach($list as $nr => $record){
        if(
            property_exists($record, 'controller') &&
            property_exists($record, 'function')
        ){
            try {
                $class = $record->controller;
                $constant =  $class . '::INFO_' . strtoupper($record->function);
                $info = false;
                if(defined($constant)) {
                    $info = constant($constant);
                }
                elseif(defined($class . '::INFO')){
                    $info = constant($class . '::INFO');
                }
                $record->info = $info;
                $result[] = $record;
            } catch (Exception $exception){
                Cli::tput('init');
                echo Cli::tput('background', CLI::COLOR_RED);
                echo PHP_EOL;
                echo PHP_EOL;
                echo $exception->getMessage() . PHP_EOL;
                echo Cli::tput('reset');
                echo PHP_EOL;
                continue;
            }
        }
    }
    return $result;
}
