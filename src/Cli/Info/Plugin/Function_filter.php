<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Config;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

function function_filter(Parse $parse, Data $data, $list, $options){
    $result = [];
    if(count($options) === 1){
        foreach($options as $key => $value){
            foreach($list as $nr => $record){
                if(
                    property_exists($record, $key) &&
                    !empty($record->{$key}) &&
                    is_array($record->{$key}) &&
                    in_array($value, $record->{$key})
                ){
                    $result[] = $record;
                }
            }
        }
    }
    return $result;
}
