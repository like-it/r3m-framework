<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Core;

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

                Core::execute('tput init');
                for($i=0; $i < 16; $i++){
                    Core::execute('tput setab', $i);
                    echo 'test' . PHP_EOL;
                    Core::execute('tput sgr0');
                }
                continue;
            }
        }
    }
    return $result;
}
