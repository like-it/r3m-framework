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
                Cli::tput('background', 0);
                //Cli::tput('color', 1);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 1);
                //Cli::tput('color', 2);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 2);
                //Cli::tput('color', 3);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 3);
                //Cli::tput('color', 4);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 4);
                //Cli::tput('color', 5);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 5);
                //Cli::tput('color', 6);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 6);
                //Cli::tput('color', 7);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 8);
                //Cli::tput('color', 9);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 10);
                //Cli::tput('color', 11);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 11);
                //Cli::tput('color', 12);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 12);
                //Cli::tput('color', 13);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 13);
                //Cli::tput('color', 14);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 14);
                //Cli::tput('color', 15);
                echo 'test' . PHP_EOL;
                Cli::tput('background', 15);
                //Cli::tput('color', 0);
                echo 'test' . PHP_EOL;
                continue;
            }
        }
    }
    return $result;
}
