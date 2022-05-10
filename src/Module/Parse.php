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

use ParseError;
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
    private $build;
    private $limit;
    private $cache_dir;
    private $local;
    private $is_assign;
    private $halt_literal;

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

    public function limit($limit=null){
        if($limit !== null){
            $this->setLimit($limit);
        }
        return $this->getLimit();
    }

    public function setLimit($limit=null){
        $this->limit= $limit;
    }

    private function getLimit(){
        return $this->limit;
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

    public function build($build=null){
        if($build !== null){
            $this->setBuild($build);
        }
        return $this->getBuild();
    }

    private function setBuild($build=null){
        $this->build= $build;
    }

    private function getBuild(){
        return $this->build;
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

    public function is_assign($is_assign=null){
        if($is_assign !== null){
            $this->is_assign = $is_assign;
        }
        return $this->is_assign;
    }

    public function halt_literal($halt_literal=null){
        if($halt_literal !== null){
            $this->halt_literal = $halt_literal;
        }
        return $this->halt_literal;
    }

    /**
     * @throws \R3m\Io\Exception\ObjectException
     * @throws \R3m\Io\Exception\FileWriteException
     */
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
            foreach($string as $key => $value){
                try {
                    $this->local($string);
                    $value = $this->compile($value, $storage->data(), $storage, $is_debug);
                    $string->$key = $value;
                } catch (Exception | ParseError $exception){
                    dd($exception);
                }

            }            
            return $string;
        }
        elseif(stristr($string, '{') === false){
            return $string;
        }
        else {
            $build = $this->build(new Build($this->object(), $this, $is_debug));
            $build->cache_dir($this->cache_dir());
            $build->limit($this->limit());
            $source = $storage->data('r3m.io.parse.view.source');
            $options = [];
            if(empty($source)){
                $options = [
                    'source' => $storage->data('r3m.io.parse.view.url')
                ];
                $url = $build->url($string, $options);
            } else {
                $options = [
                    'source' => $storage->data('r3m.io.parse.view.source.url'),
                    'parent' => $storage->data('r3m.io.parse.view.url')
                ];
                $url = $build->url($string, $options);
            }
            $storage->data('r3m.io.parse.compile.url', $url);
            $storage->data('this', $this->local());
            $mtime = $storage->data('r3m.io.parse.view.mtime');            
            if(File::exist($url) && File::mtime($url) == $mtime){
                //cache file                   
                $class = $build->storage()->data('namespace') . '\\' . $build->storage()->data('class');
                $template = new $class(new Parse($this->object()), $storage);                
                if(empty($this->halt_literal())){
                    $string = Literal::apply($storage, $string);
                }                
                $string = $template->run();
                if(empty($this->halt_literal())){
                    $string = Literal::restore($storage, $string);
                }
                
                $storage->data('delete', 'this');
                return $string;
            }
            elseif(File::exist($url) && File::mtime($url) != $mtime){
                opcache_invalidate($url, true);
            }
            $string = str_replace('{{literal}}', '{literal}', $string);
            $string = str_replace('{{/literal}}', '{/literal}', $string);
            if(empty($this->halt_literal())){
                $string = literal::apply($storage, $string);
            }
            $string = str_replace('{{R3M}}', '{R3M}', $string);
            $explode = explode('{R3M}', $string, 2);
            if(array_key_exists(1, $explode)){
                $storage->data('r3m.io.parse.compile.remove_newline', true);
                $string = str_replace(
                    [
                        '{',
                        '}',
                    ],
                    [
                        '[$ldelim]',
                        '[$rdelim]',
                    ],
                    $explode[1]
                );
                $string = str_replace(
                    [
                        '[$ldelim]',
                        '[$rdelim]',
                    ],
                    [
                        '{$ldelim}',
                        '{$rdelim}',
                    ],
                    $string
                );
                $string = str_replace(
                    [
                        '{$ldelim}{$ldelim}',
                        '{$rdelim}{$rdelim}',
                    ],
                    [
                        '{',
                        '}',
                    ],
                    $string
                );
                $string = ltrim($string, " \t\n\r\0\x0B");
            }
            $tree = Token::tree($string, $is_debug);
            dd($tree);
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
            $document = $build->document($storage, $tree, $document);
            $document = $build->create('run', $tree, $document);
            $document = $build->create('require', $tree, $document);
            $document = $build->create('use', $tree, $document);
            $document = $build->create('trait', $tree, $document);
            $write = $build->write($url, $document);
            if($mtime !== null){
                $touch = File::touch($url, $mtime);
                opcache_invalidate($url, true);
                if(opcache_is_script_cached($url) === false){
                    $status = opcache_get_status(true);
                    if($status !== false){
                        opcache_compile_file($url);
                    }
                }
            }
            $class = $build->storage()->data('namespace') . '\\' . $build->storage()->data('class');
            $exists = class_exists($class);
            if($exists){
                $template = new $class(new Parse($this->object()), $storage);
                $string = $template->run();
                if(empty($this->halt_literal())){
                    $string = Literal::restore($storage, $string);
                }
                $storage->data('delete', 'this');
            } else {
                if(File::exist($url)){
                    //not ready yet
                    sleep(1);
                    $write = implode("\n", $document);
                    $written = File::write($url, $write);
                    require $url;;
                    $exists = class_exists($class);
                    if($exists){
                        $template = new $class(new Parse($this->object()), $storage);
                        $string = $template->run();
                        if(empty($this->halt_literal())){
                            $string = Literal::restore($storage, $string);
                        }
                        $storage->data('delete', 'this');
                        return $string;
                    } else {
                        throw new Exception('Class ('. $class .') doesn\'t exist');
                    }
                }
                //not ready yet
                sleep(1);
                $exists = class_exists($class);
                if($exists){
                    $template = new $class(new Parse($this->object()), $storage);
                    $string = $template->run();
                    if(empty($this->halt_literal())){
                        $string = Literal::restore($storage, $string);
                    }
                    $storage->data('delete', 'this');
                } else {
                    throw new Exception('Class ('. $class .') doesn\'t exist');
                }
            }
        }
        return $string;
    }

    public static function readback($object, $parse, $type=null){
        $data = $parse->storage()->data($type);
        if(is_array($data)){
            foreach($data as $key => $value){
                $data[$key] = Literal::restore($parse->storage(), $value);
            }
        }
        elseif(is_object($data)){
            foreach($data as $key => $value){                
                $data->$key = Literal::restore($parse->storage(), $value);
            }
        } else {
            $data = Literal::restore($parse->storage(), $data);
        }
        $object->data($type, $data);
        return $data;
    }
}