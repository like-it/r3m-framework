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
use R3m\Io\Module\Parse\Literal;

use stdClass;

class Parse {
    const PLUGIN = 'Plugin';
    const TEMPLATE = 'Template';
    const COMPILE = 'Compile';

    private $object;
    private $storage;
    private $cache_dir;

    public function __construct($object){
        $this->object($object);
        $this->storage(new Data());
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

    public function compile($string='', $data=[], $storage=null, $is_debug=false){
        if($storage === null){
            $storage = $this->storage(new Data());
        }
        $storage->data(Core::object_merge($storage->data(), $data));

        if(is_array($string)){
            foreach($string as $key => $value){
                $string[$key] = $this->compile($value, $data, $storage, $is_debug);
            }
        }
        elseif(is_object($string)){
            foreach($string as $key => $value){
                $string->$key = $this->compile($value, $data, $storage, $is_debug);
            }
        }
        elseif(stristr($string, '{') === false){
            /*
            d($string);
            if(substr($string, 0, 1) == '"' && substr($string, -1, 1) == '"'){
                return str_replace(['\n', '\t'],["\n", "\t"], substr($string, 1, -1));
            }
            */
            return $string;
        }
        else {
//             $string = str_replace('&quot;', '"', $string);
            $build = new Build($this->object());
            $build->cache_dir($this->cache_dir());
            $url = $build->url($string);

            $storage->data('r3m.parse.compile.url', $url);
            $mtime = $storage->data('r3m.parse.view.mtime');

//             opcache_invalidate($url, true);
            if(File::exist($url) && File::mtime($url) == $mtime){
                //cache file
                $meta = $build->meta();
                $class = $meta['namespace'] . '\\' . $meta['class'];
                $template = new $class(new Parse($this->object()), $storage);

                $string = $template->run();
                $string = Literal::restore($string, $storage);
                return $string;
            }

            /*
            elseif(File::exist($url) && File::mtime($url) != $mtime){
                opcache_invalidate($url, true);
            }
            */

            $string = literal::apply($string, $storage);
//             $is_debug = 1;
            $tree = Token::tree($string, $is_debug);
            $tree = $build->require('function', $tree);
            $tree = $build->require('modifier', $tree);

//             d($tree);

            $build_storage = $build->storage();
            $document = $build_storage->data('document');
            if(empty($document)){
                $document = [];
            }
            $document = $build->create('header', $document);
            $document = $build->create('class', $document);

            $build->indent(2);

            $document = $build->document($tree, $document, $storage);
            $document = $build->create('run', $document);
            $document = $build->create('require', $document);
            $document = $build->create('use', $document);

            $write = $build->write($url, $document);

            if($mtime !== null){
                File::touch($url, $mtime);

                /*
                opcache_invalidate($url, true);

                if(opcache_is_script_cached($url) === false){
                    opcache_compile_file($url);
                }
                */
            }
            $class = $build->storage()->data('namespace') . '\\' . $build->storage()->data('class');
            $template = new $class(new Parse($this->object()), $storage);

            $string = $template->run();

            $string = Literal::restore($string, $storage);

//             $string = str_replace("\n", "\n", $string);
        }
        return $string;
    }

}