<?php

namespace R3m\Io;

use Smarty;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Handler;
use R3m\Io\Module\Host;
use R3m\Io\Module\Autoload;
use R3m\Io\Module\Route;

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

    public static function view($object, $template){
        $smarty = new Smarty();        
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);  
        /*
        foreach($config->data() as $key => $value){
            $object->data('smarty.config.' . $key, $value);
        }
        */
        $url =  $config->data('host.dir.view') . $template . $config->data('extension.template');
        /*
        $data = $object->data('smarty');
        foreach($data as $key => $value){
            $smarty->assign($key, $value);
        } 
        */
        $data = $object->data();
        foreach($data as $key => $value){
            $smarty->assign($key, $value);
        }
        $fetch = trim($smarty->fetch($url));
        return $fetch;        
    }
    
    public static function run($object){
        Handler::request($object); 
        Host::configure($object);
        
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);
        
//         dd($config->data());
        
        
        Autoload::configure($object);
        Route::configure($object);
        
        $route = Route::request($object);
                
//         dd($route);
        
        if($route === false){
            $object->data('smarty.request', Route::input($object)->request);            
            echo App::view($object, '404');
            die;
        } else {
            //use the controller...
                        
            $result = $route->controller::{$route->function}($object);
            
            
            
            echo App::view($object, '404');
            die;
            dd($object->data());
//             $page = App::view($object, '404');
        }
        
          
        
        /*
        d($object->data(__NAMESPACE__ . '.config')->data('framework.dir'));
        
        if(file_exists($filename))
        
        */
        dd($route);
        
        //view is a data object
        /**
         * containing a 
         * - config data object
         * - data object
         * - template data object
         */
    }

    public static function is_cli(){
        if(!defined('IS_CLI')){
            return Core::is_cli();
        } else {
            return true;
        }
    }

}

