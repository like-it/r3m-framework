<?php

namespace R3m\Io;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Handler;
use R3m\Io\Module\Host;
use R3m\Io\Module\Autoload;
use R3m\Io\Module\Route;
use R3m\Io\Module\File;
use R3m\Io\Module\View;

use Exception;

class App extends Data {    
    public const NAMESPACE = __NAMESPACE__;   
    public const NAME = 'App';
    
    public function __construct($autoload, $config){
        $this->data(App::NAMESPACE . '.Autoload.Composer', $autoload);
        $this->data(App::NAMESPACE . '.' . Config::NAME, $config);               
        App::is_cli();                        
        require_once 'debug.php';        
    }   
    
    public static function run($object){
        Handler::request_configure($object); 
        Host::configure($object); 
        View::configure($object);
//         $config = $object->data(App::NAMESPACE . '.' . Config::NAME);
        Autoload::configure($object);
        Route::configure($object);        
        $route = Route::request($object);                              
        if($route === false){
            $object->data('smarty.request', Route::input($object)->request);            
            echo View::get($object, '404');
            die;
        } else {
            $result = $route->controller::{$route->function}($object);
//             dd($result);
            return $result;
        }        
    }
    
    public function request($attribute=null, $value=null){
        $object = $this;
        
        dd($object->data());
        //App::NAMESPACE . '.' . Config::NAME
        
    }
    
    public function session($attribute=null, $value=null){        
        return Handler::session($attribute, $value);
    }
    
    public function cookie($attribute=null, $value=null){
        return Handler::cookie($attribute, $value);
    }
    
    public static function is_cli(){
        if(!defined('IS_CLI')){
            return Core::is_cli();
        } else {
            return true;
        }
    }

}

