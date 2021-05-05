<?php
/**
 * @author          Remco van der Velde
 * @since           2021-03-05
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Core;

function function_import(Parse $parse, Data $data, $url=null, $controller=null, $collection=null){
    $object = $parse->object();
    if(is_array($controller)){
        $collection = $controller;
        $controller = null;
    }
    $extension = strtolower(File::extension($url));
    $name = '';
    $value = null;
    switch($extension){
        case 'js' :
            if($controller !== null){
                $location = [];
                $location[] = $object->config('host.dir.root') .
                    $object->config('dictionary.public') .
                    $object->config('ds') .
                    ucfirst($controller) .
                    $object->config('ds') .
                    ucfirst($extension) .
                    $object->config('ds') .
                    $url;
                $location[] = $object->config('host.dir.root') .
                    ucfirst($controller) .
                    $object->config('ds') .
                    $object->config('dictionary.public') .
                    $object->config('ds') .
                    ucfirst($extension) .
                    $object->config('ds') .
                    $url;
                $location[] = $object->config('host.dir.root') .
                    ucfirst($controller) .
                    $object->config('ds') .
                    $object->config('dictionary.view') .
                    $object->config('ds') .
                    ucfirst($extension) .
                    $object->config('ds') .
                    $url;
            } else {
                $location = [];
                $location[] = $object->config('controller.dir.public') .
                    ucfirst($extension) .
                    $object->config('ds') .
                    $url;
                $location[] = $object->config('controller.dir.view') .
                    ucfirst($extension) .
                    $object->config('ds') .
                    $url;
                $location[] = $url;
            }
            $file = false;
            foreach($location as $find){
                if(File::exist($find)){
                    $file = $find;
                    break;
                }
            }
            if($file === false){
                return;
            }
            $name = 'script';
            $value = [];
            $value[] = '<script type="text/javascript">';
            if(is_array($collection)){
                $value[] = 'ready(function(){';
                foreach($collection as $key => $val) {
                    if (is_string($val)) {
                        $val = '\'' . $val . '\'';
                    } elseif (is_array($val) || is_object($val)) {
                        $val = Core::OBJECT($val, Core::OBJECT_JSON);
                    }
                    $key = '\'' . $key . '\'';
                    $value[] = 'priya.collection(' . $key . ', ' . $val . ');';
                }
                $value[] = '});';
            }
            $value[] = File::read($file);
            $value[] = "\t\t\t" . '</script>';
            $value = implode("\n", $value);
        break;
        case 'css' :
            if($controller !== null){
                $location = $object->config('host.dir.root') .
                    ucfirst($controller) .
                    $object->config('ds') .
                    $object->config('dictionary.public') .
                    $object->config('ds') .
                    ucfirst($extension) .
                    $object->config('ds') .
                    $url;
                if(!File::exist($location)){
                    //return;
                }
                $href = $data->data('host.url') .
                    ucfirst($controller) .
                    $object->config('ds') .
                    ucfirst($extension) .
                    $object->config('ds') .
                    $url;
            } else {
                $location = $object->config('controller.dir.public') .
                    ucfirst($extension) .
                    $object->config('ds') .
                    $url;
                if(!File::exist($location)){
                    //return;
                }
                $href = $data->data('host.url') .
                    $object->config('controller.title') .
                    $object->config('ds') .
                    ucfirst($extension) .
                    $object->config('ds') .
                    $url;
            }
            $name = 'link';
            $value =  '<link rel="stylesheet" href="' .  $href . '?version=' . $object->config('framework.version') . '">';
        break;
    }
    $list = $data->data($name);
//    dd($list);
    if(empty($list)){
        $list = [];
    }
    $list[] = $value;
    $data->data($name, $list);
}
