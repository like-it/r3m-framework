<?php
/**
 * @author          Remco van der Velde
 * @since           2021-03-05
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Core;

function function_script(Parse $parse, Data $data, $name='script', $script=null){
    $object = $parse->object();
    if($name === 'ready'){
        $name = 'script';
        $value = [];
        $value[] = '<script type="text/javascript">';
        $value[] = 'ready(() => {';
        $value[] = $script;
        $value[] = '});';
        $value[] = "\t\t\t" . '</script>';
    }
    elseif($name === 'module'){
        $name = 'script';
        $value = [];
        $value[] = '<script type="module">';
        $value[] = $script;
        $value[] = "\t\t\t" . '</script>';
    }
    else {
        $value = [];
        $value[] = '<script type="text/javascript">';
        $value[] = $script;
        $value[] = "\t\t\t" . '</script>';
    }
    $list = $data->data($name);
    if(is_string($list)){
        d($list);
        d($data->data());
        dd($name);
    }
    if(empty($list)){
        $list = [];
    }
    $value = implode("\n", $value);
    $list[] = $value;
    $data->data($name, $list);
}
