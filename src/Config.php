<?php

namespace R3m\Io;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;

class Config extends Data {
    public const NAME = 'Config';
    
    public const MODE_PRODUCTION = 'production';
    public const MODE_DEVELOPMENT = 'development';
    
    public const VALUE_DS = DIRECTORY_SEPARATOR;
    
    public const DATA = Config::VALUE_DATA;
    public const VALUE_DATA = 'data';
    
    public const HTML = Config::VALUE_HTML;
    public const VALUE_HTML = 'public';
    
    public const HOST = Config::VALUE_HOST;
    public const VALUE_HOST = 'host';
    
    public const CACHE = 'cache';
    public const VALUE_CACHE = '/tmp/r3m/io/';
    
    public const SOURCE = 'source';
    public const VALUE_SOURCE = 'src';
    
    public const CLI = 'cli';
    public const VALUE_CLI = 'Cli';
    
    public const MODULE = 'module';
    public const VALUE_MODULE = 'Module';
    
    public const FRAMEWORK = 'framework';
    public const VALUE_FRAMEWORK = 'r3m/framework';
    
    public const ENVIRONMENT = 'environment';
    public const VALUE_ENVIRONMENT = Config::MODE_PRODUCTION;
    
    public const DS = 'ds';
    
    public const VIEW = 'view';
    public const VALUE_VIEW = 'view';
    
    public const LOCALHOST_EXTENSION = 'localhost.extension';
    public const VALUE_LOCALHOST_EXTENSION =  [
        'local',
        'develop'
    ];
    
    public const ROUTE = 'route.json';
    
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
        $this->data(Config::DATA, Config::VALUE_DATA);
        $this->data(Config::SOURCE, Config::VALUE_SOURCE);
        $this->data(Config::CACHE, Config::VALUE_CACHE);
        $this->data(Config::HTML, Config::VALUE_HTML);
        $this->data(Config::MODULE, Config::VALUE_MODULE);
        $this->data(Config::CLI, Config::VALUE_CLI);
        $this->data(Config::HOST, Config::VALUE_HOST);
        $this->data(Config::VIEW, Config::VALUE_VIEW);
        $this->data(Config::FRAMEWORK, Config::VALUE_FRAMEWORK);
        $this->data(Config::ENVIRONMENT, Config::VALUE_ENVIRONMENT);
        $this->data(Config::DS, Config::VALUE_DS);
        
        $this->data('extension.php', '.php');
        $this->data('extension.json', '.json');
        $this->data('extension.css', '.css');
        $this->data('extension.js', '.js');
        $this->data('extension.jpg', '.jpg');
        $this->data('extension.gif', '.gif');
        $this->data('extension.png', '.png');
        $this->data('extension.zip', '.zip');
        $this->data('extension.rar', '.rar');        
        $this->data('extension.template', '.tpl');
        
        $this->data(Config::LOCALHOST_EXTENSION, Config::VALUE_LOCALHOST_EXTENSION);        
        
        $this->data('project.dir.source', $this->data('project.dir.root') . $this->data(Config::SOURCE) . $this->data(Config::DS));
        $this->data('project.dir.data', $this->data('project.dir.root') . $this->data(Config::DATA) . $this->data(Config::DS));
        $this->data('project.dir.cli', $this->data('project.dir.root') . $this->data(Config::CLI) . $this->data(Config::DS));
        $this->data('project.dir.public', $this->data('project.dir.root') . $this->data(Config::HTML) . $this->data(Config::DS));
        $this->data('project.dir.host', $this->data('project.dir.root') . $this->data(Config::HOST) . $this->data(Config::DS));
        
        
        $this->data('project.route.filename', Config::ROUTE);
        
            
        $this->data('framework.dir.root', $this->data('project.dir.vendor') . $this->data(Config::FRAMEWORK) . $this->data(Config::DS));
//         $this->data('framework.dir.source', $this->data('framework.dir.root') . $this->data(Config::SOURCE) . $this->data(Config::DS));
        $this->data('framework.dir.data', $this->data('framework.dir.root') . $this->data(Config::DATA) . $this->data(Config::DS));
        $this->data('framework.dir.cache', $this->data(Config::CACHE) . Config::FRAMEWORK . $this->data(Config::DS));
        $this->data('framework.dir.module', $this->data('framework.dir.source') . $this->data(Config::MODULE) . $this->data(Config::DS));
        $this->data('framework.dir.cli', $this->data('framework.dir.root') . $this->data(Config::CLI) . $this->data(Config::DS));       
    }
}