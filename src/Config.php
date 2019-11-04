<?php

namespace R3m\Io;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;

class Config extends Data {
    public const NAME = 'Config';
    
    public const MODE_PRODUCTION = 'production';
    public const MODE_DEVELOPMENT = 'development';
    
    public const VALUE_DS = DIRECTORY_SEPARATOR;
    
    public const DATA = 'data';
    public const VALUE_DATA = 'Data';
    
    public const PUBLIC = 'public';
    public const VALUE_PUBLIC = 'Public';
    
    public const HOST = 'host';
    public const VALUE_HOST = 'Host';
    
    public const CACHE = 'cache';
    public const VALUE_CACHE = '/tmp/r3m/io/';
       
    public const SOURCE = 'Source';
    public const VALUE_SOURCE = 'src';
    
    public const CLI = 'cli';
    public const VALUE_CLI = 'Cli';
    
    public const MODULE = 'module';
    public const VALUE_MODULE = 'Module';
    
    public const FRAMEWORK = 'framework';
    public const VALUE_FRAMEWORK = 'r3m/framework';
    
    public const ENVIRONMENT = 'environment';
    public const VALUE_ENVIRONMENT = Config::MODE_PRODUCTION;
    
    public const PLUGIN = 'plugin';
    public const VALUE_PLUGIN = 'Plugin';
    
    public const DS = 'ds';
    
    public const VIEW = 'view';
    public const VALUE_VIEW = 'View';
    
    public const LOCALHOST_EXTENSION = 'localhost.extension';
    public const VALUE_LOCALHOST_EXTENSION =  [
        'local',
        'develop'
    ];
    
    public const ROUTE = 'Route.json';
    
    public const DICTIONARY = 'dictionary';
    
    public function __construct($config=[]){
        if(array_key_exists('dir.vendor', $config)){
            $this->data('project.dir.vendor', $config['dir.vendor']);
            $this->data('project.dir.root', dirname($this->data('project.dir.vendor')) . '/');
            unset($config['dir.vendor']);
        }        
        $this->default();
        foreach($config as $attribute => $value){
            $this->data($attribute, $value);
        }
        //         $this->register();
    }
    
    public function default(){
        $key = Config::DICTIONARY . '.' . Config::DATA;
        $value = Config::VALUE_DATA;
        $this->data($key, $value);
        
        $key = Config::DICTIONARY . '.' . Config::SOURCE;
        $value = Config::VALUE_SOURCE;
        $this->data($key, $value);
                
        $key = Config::DICTIONARY . '.' . Config::CACHE;
        $value = Config::VALUE_CACHE;
        $this->data($key, $value);
        
        $key = Config::DICTIONARY . '.' . Config::PUBLIC;
        $value = Config::VALUE_PUBLIC;
        $this->data($key, $value);
        
        $key = Config::DICTIONARY . '.' . Config::MODULE;
        $value = Config::VALUE_MODULE;
        $this->data($key, $value);
        
        $key = Config::DICTIONARY . '.' . Config::CLI;
        $value = Config::VALUE_CLI;
        $this->data($key, $value);
        
        $key = Config::DICTIONARY . '.' . Config::HOST;
        $value = Config::VALUE_HOST;
        $this->data($key, $value);
        
        $key = Config::DICTIONARY . '.' . Config::VIEW;
        $value = Config::VALUE_VIEW;
        $this->data($key, $value);
        
        $key = Config::DICTIONARY . '.' . Config::FRAMEWORK;
        $value = Config::VALUE_FRAMEWORK;
        $this->data($key, $value);
        
        $key = Config::DICTIONARY . '.' . Config::ENVIRONMENT;
        $value = Config::VALUE_ENVIRONMENT;
        $this->data($key, $value);
        
        $key = Config::DICTIONARY . '.' . Config::PLUGIN;
        $value = Config::VALUE_PLUGIN;
        $this->data($key, $value);
        
        $key = Config::DICTIONARY . '.' . Config::DS;
        $value = Config::VALUE_DS;
        $this->data($key, $value);
        
        $value = Config::VALUE_DS;
        $key = Config::DS;
        $this->data($key, $value);
                        
        $this->data('extension.php', '.php');
        $this->data('extension.json', '.json');
        $this->data('extension.css', '.css');
        $this->data('extension.js', '.js');
        $this->data('extension.jpg', '.jpg');
        $this->data('extension.gif', '.gif');
        $this->data('extension.png', '.png');
        $this->data('extension.zip', '.zip');
        $this->data('extension.rar', '.rar');        
        $this->data('extension.tpl', '.tpl');
        
        $this->data(Config::LOCALHOST_EXTENSION, Config::VALUE_LOCALHOST_EXTENSION);        
        
        $key = 'project.dir.source';
        $value = 
            $this->data('project.dir.root') . 
            $this->data(Config::DICTIONARY . '.' . Config::SOURCE) . 
            $this->data(Config::DS);        
        $this->data($key, $value);  
        
            
        $key = 'project.dir.data';
        $value = 
            $this->data('project.dir.root') . 
            $this->data(Config::DICTIONARY . '.' . Config::DATA) . 
            $this->data(Config::DS);
        $this->data($key, $value);                    
                        
        $key = 'project.dir.cli';
        $value = 
            $this->data('project.dir.root') . 
            $this->data(Config::DICTIONARY . '.' . Config::CLI) . 
            $this->data(Config::DS);        
        $this->data($key, $value);
            
        
        
        $key = 'project.dir.public';        
        $value = 
            $this->data('project.dir.root') . 
            $this->data(Config::DICTIONARY . '.' . Config::PUBLIC) . 
            $this->data(Config::DS);
        $this->data($key, $value);
                              
        $key = 'project.dir.host';
        $value = 
            $this->data('project.dir.root') . 
            $this->data(Config::DICTIONARY . '.' . Config::HOST) . 
            $this->data(Config::DS);
        $this->data($key, $value);
                
        $key = 'project.route.filename';
        $value = Config::ROUTE;
        $this->data($key, $value);
        
        //project.route.url can be configured in index / cli
        
        $key = 'framework.dir.root';
        $value = 
            $this->data('project.dir.vendor') . 
            $this->data(Config::DICTIONARY . '.' . Config::FRAMEWORK) . 
            $this->data(Config::DS);
        $this->data($key, $value);
        
        $key = 'framework.dir.source';
        $value = 
            $this->data('framework.dir.root') . 
            $this->data(Config::DICTIONARY . '.' . Config::SOURCE) . 
            $this->data(Config::DS);
        $this->data($key, $value);
                       
        $key = 'framework.dir.data';
        $value =
            $this->data('framework.dir.root') . 
            $this->data(Config::DICTIONARY . '.' . Config::DATA) . 
            $this->data(Config::DS);                         
        $this->data($key, $value);
                       
        $key = 'framework.dir.cache';
        $value = 
            $this->data(Config::DICTIONARY . '.' . Config::CACHE) . 
            $this->data(Config::DICTIONARY . '.' . Config::FRAMEWORK) . 
            $this->data(Config::DS);
        $this->data($key, $value);
                     
        $key = 'framework.dir.module';
        $value = 
            $this->data('framework.dir.source') . 
            $this->data(Config::DICTIONARY . '.' . Config::MODULE) . 
            $this->data(Config::DS);          
        $this->data($key, $value);
                           
        $key = 'framework.dir.cli';
        $value = 
            $this->data('framework.dir.root') . 
            $this->data(Config::DICTIONARY . '.' . Config::CLI) . 
            $this->data(Config::DS);
        $this->data($key, $value);  
        
        $key = 'framework.environment';
        $value = $this->data(Config::DICTIONARY . '.' . Config::ENVIRONMENT);        
        $this->data($key, $value);                 
    }
    
    public static function ucfirst_sentence($string='', $delimiter='.'){
        $explode = explode($delimiter, $string);        
        foreach($explode as $nr => $part){
            $explode[$nr] = ucfirst(trim($part));
        }
        return implode($delimiter, $explode);
    }
    
}