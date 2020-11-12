<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Parse\Token;
use R3m\Io\Module\Parse\Build;
use R3m\Io\Module\Parse\Literal;

use stdClass;

class Parse {
    const PLUGIN = 'Plugin';
    const TEMPLATE = 'Template';
    const COMPILE = 'Compile';

    private $object;
    private $storage;
    private $cache_dir;
    private $local;

    public function __construct($object, $storage=null){
        $this->object($object);
        $this->configure();
        if($storage === null){
            $this->storage(new Data());
        } else {
            $this->storage($storage);
        }
    }

    private function configure(){
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
            $config->data('framework.dir.plugin', $config->data('framework.dir.source') . Parse::PLUGIN . $config->data('ds'));
        }
        $dir_plugin = $config->data('controller.dir.plugin');
        if(empty($dir_plugin)){
            $config->data('controller.dir.plugin', $config->data('controller.dir.root') . Parse::PLUGIN . $config->data('ds'));
        }
        $compile = $config->data('dictionary.compile');
        if(empty($compile)){
            $config->data('dictionary.compile', Parse::COMPILE);
        }
        $template = $config->data('dictionary.template');
        if(empty($template)){
            $config->data('dictionary.template', Parse::TEMPLATE);
        }
        $cache_dir = $config->data('project.dir.data') . $config->data('dictionary.compile') . $config->data('ds');
        $this->cache_dir($cache_dir);
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

    public function storage($storage=null){
        if($storage !== null){
            $this->setStorage($storage);
        }
        return $this->getStorage();
    }

    private function setStorage($storage=null){
        $this->storage = $storage;
    }

    private function getStorage(){
        return $this->storage;
    }

    public function cache_dir($cache_dir=null){
        if($cache_dir !== null){
            $this->cache_dir = $cache_dir;
        }
        return $this->cache_dir;
    }

    public function local($local=null){
        if($local !== null){
            $this->local = $local;
        }        
        return $this->local;
    }

    public function compile($string='', $data=[], $storage=null, $is_debug=false){
        if($storage === null){
            $storage = $this->storage(new Data());
        }
        if(is_object($data)){
            $storage->data(Core::object_merge($storage->data(), $data));
        } else {
            $storage->data($data);
        }
        if(is_array($string)){
            foreach($string as $key => $value){
                $string[$key] = $this->compile($value, $storage->data(), $storage, $is_debug);
            }
        }
        elseif(is_object($string)){                                                 
            $this->local($string);
            foreach($string as $key => $value){                 
                $value = $this->compile($value, $storage->data(), $storage, $is_debug);
                $string->$key = $value;
            }            
            return $string;
        }
        elseif(stristr($string, '{') === false){
            return $string;
        }
        else {
            $build = new Build($this->object(), $is_debug);
            $build->cache_dir($this->cache_dir());
            $source = $storage->data('r3m.io.parse.view.source');
            if(empty($source)){
                $url = $build->url($string, [
                    'source' => $storage->data('r3m.io.parse.view.url')
                ]);
            } else {
                $url = $build->url($string, [
                    'source' => $storage->data('r3m.io.parse.view.source.url'),
                    'parent' => $storage->data('r3m.io.parse.view.url')
                ]);
            }
            $storage->data('r3m.io.parse.compile.url', $url);
            $storage->data('this', $this->local());
            $mtime = $storage->data('r3m.io.parse.view.mtime');            
            if(File::exist($url) && File::mtime($url) == $mtime){
                //cache file
                $meta = $build->meta();
                $class = $meta['namespace'] . '\\' . $meta['class'];
                $template = new $class(new Parse($this->object()), $storage);

                $string = $template->run();
                $string = Literal::restore($string, $storage);
                $storage->data('delete', 'this');
                return $string;
            }
            /*
            elseif(File::exist($url) && File::mtime($url) != $mtime){
                opcache_invalidate($url, true);
            }
            */
            $string = literal::apply($string, $storage);            
            $tree = Token::tree($string, $is_debug);
            // dd($tree);
            $tree = $build->require('function', $tree);
            $tree = $build->require('modifier', $tree);
            $build_storage = $build->storage();
            $document = $build_storage->data('document');
            if(empty($document)){
                $document = [];
            }
            $document = $build->create('header', $tree, $document);
            $document = $build->create('class', $tree, $document);
            $build->indent(2);
            $document = $build->document($tree, $document, $storage);
            $document = $build->create('run', $tree, $document);
            $document = $build->create('require', $tree, $document);
            $document = $build->create('use', $tree, $document);            
            $write = $build->write($url, $document);
            if($mtime !== null){
                $touch = File::touch($url, $mtime);
                /*
                opcache_invalidate($url, true);
                if(opcache_is_script_cached($url) === false){
                    opcache_compile_file($url);
                }
                */
            }
            $class = $build->storage()->data('namespace') . '\\' . $build->storage()->data('class');
            $exists = class_exists($class);
            if($exists){
                $template = new $class(new Parse($this->object()), $storage);
                $string = $template->run();
                $string = Literal::restore($string, $storage);
                $storage->data('delete', 'this');
            }
        }
        return $string;
    }

    public static function readback($object, $parse, $type=null){
        $data = $parse->storage()->data($type);
        if(is_array($data)){
            foreach($data as $key => $value){
                $data[$key] = Literal::restore($value, $parse->storage());
            }
        }
        elseif(is_object($data)){
            foreach($data as $key => $value){
                $data->$key = Literal::restore($value, $parse->storage());
            }
        } else {
            $data = Literal::restore($data, $parse->storage());
        }
        return $object->data($type, $data);
    }
}