<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module;

use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Parse\Token;
use R3m\Io\Module\Parse\Build;

use stdClass;

class Parse {
    public const PLUGIN = 'Plugin';
    
    private $object;
    
    public function __construct($object){
        $this->object($object);
        
        $config = $this->object()->data(App::NAMESPACE . '.' . Config::NAME);
        
        $dir_plugin = $config->data('project.dir.plugin');
        if(empty($dir_plugin)){
            $config->data('project.dir.plugin', $config->data('project.dir.root') . Parse::PLUGIN . $config->data('ds'));
        }
        
        $dir_plugin = $config->data('host.dir.plugin');
        if(empty($dir_plugin)){
            $config->data('host.dir.plugin', $config->data('host.dir.root') . Parse::PLUGIN . $config->data('ds'));
        }
        
        $dir_plugin = $config->data('framework.dir.plugin');
        if(empty($dir_plugin)){
            $config->data('framework.dir.plugin', $config->data('framework.dir.root') . Parse::PLUGIN . $config->data('ds'));
        }
        
    }
    
    public function object($object=null){
        if($object !== null){
            $this->setObject($object);
        }
        return $this->getObject();
    }
 
    private function setObject($object=null){
        $this->object= $object;
    }
    
    private function getObject(){
        return $this->object;
    }
    
    public function compile($string='', $data=[]){
        
        $storage = new Data();
        $storage->data($data);
        
//         $key = sha1($string);
        
//         $this->object->data('build.compile.key', $key);
        
        $build = new Build($this->object());
        $url = $build->url($string);
        
        if(File::exist($url)){
            //cache file
        }        
        $tree = Token::tree($string);
        
        $tree = $build->require('function', $tree);
        $tree = $build->require('modifier', $tree);
        
        
        $storage = $build->storage();
        $document = $storage->data('document');
        if(empty($document)){
            $document = [];
        }        
        $document = $build->create('header', $document);
        $document = $build->create('require', $document);
        $document = $build->create('use', $document);
        
        $document = $build->document($tree, $document);
        
        
        
        $write = $build->write($url, $document);
        
        
//         $tree = $build->add('plugin')
        
//         $config = $this->object()->data(App::NAMESPACE . '.' . Config::NAME);
                       
        
        
        
        
        
        d($build->storage());
        
        
        
        /**
         * require functions, modifiers
         * 
         * } replace with ; in document (for ending)
         */
        
        dd($tree);
        
        $document = Document::write($token, $storage, $this->object());
        
        dd($token);
        
        
//         $tag = Tag::create($string); 
/*
        foreach($tag as $nr => $record){
            if(array_key_exists('tag', $record)){
                $record = Tag::explore($record);                     
                $record = Variable::assign($record, $storage);
                $record = Variable::define($record, $storage);
                $record = Method::create($record, $storage);
                
                $tag[$nr] = $record;                               
            }
        }
  */      
        
        dd($tag);
        return $string;
        
    }
    
}