<?php 

namespace R3m\Io;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;

class Config extends Data {
    public const MODE_PRODUCTION = 'production';
    public const MODE_DEVELOPMENT = 'development';
    
    public const VALUE_DATA = 'data';
    public const VALUE_HTML = 'public';
    public const VALUE_ENVIRONMENT = Config::MODE_PRODUCTION;
    
    public const VALUE_DS = DIRECTORY_SEPARATOR;  
    
    public const DATA = Config::VALUE_DATA;
    public const HTML = Config::VALUE_HTML;
    public const ENVIRONMENT = 'environment';
    
    public const DS = 'ds';                        

    public function __construct($config=[]){
        $this->default();
        foreach($config as $attribute => $value){
            $this->data($attribute, $value);
        }
//         $this->register();
    }
    
    public function default(){
        $this->data(Config::DATA, Config::VALUE_DATA);
        $this->data(Config::HTML, Config::VALUE_HTML);
        $this->data(Config::ENVIRONMENT, Config::VALUE_ENVIRONMENT);
        $this->data(Config::DS, Config::VALUE_DS);
        
        $this->data('project.dir.root', dirname(__DIR__, 3) . $this->data(Config::DS));
        
        var_dump($this->data());
        die;
        
        
    }
}


