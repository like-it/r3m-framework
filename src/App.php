<?php

namespace R3m\Io;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;

use Exception;

class App extends Data {
   
    public function __construct($autoload, $config){
        $this->data(__NAMESPACE__ . '.autoload', $autoload);
        $this->data(__NAMESPACE__ . '.config', $config);               
        App::is_cli();
        require_once 'debug.php';
        
        d(__NAMESPACE__);
        dd($this->data());
        
        
        
    }

    public static function run($object){
    }

    public static function is_cli(){
        if(!defined('IS_CLI')){
            return Core::is_cli();
        } else {
            return true;
        }
    }

}

