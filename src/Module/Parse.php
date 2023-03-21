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

use stdClass;

use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Parse\Token;
use R3m\Io\Module\Parse\Build;
use R3m\Io\Module\Parse\Literal;

use Exception;
use ParseError;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\FileWriteException;

class Parse {
    const PLUGIN = 'Plugin';
    const TEMPLATE = 'Template';
    const COMPILE = 'Compile';

    const THIS_RESERVED_WORDS = [
        '#parentNode',
        '#rootNode',
        '#key',
        '#attribute'
    ];

    private $object;
    private $storage;
    private $build;
    private $limit;
    private $cache_dir;
    private $local;
    private $is_assign;
    private $halt_literal;
    private $use_this;

    private $key;

    public function __construct($object, $storage=null){
        $this->object($object);
        $this->configure();
        if($storage === null){
            $this->storage(new Data());
        } else {
            $this->storage($storage);
        }
    }

    /**
     * @throws ObjectException
     */
    private function configure(){
        $id = posix_geteuid();
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
        if(
            $config->data('ramdisk.url') &&
            !empty($config->data('ramdisk.is.disabled'))
        ){
            $cache_dir =
                $config->data('ramdisk.url') .
                $config->data(Config::POSIX_ID) .
                $config->data('ds') .
                $config->data('dictionary.compile') .
                $config->data('ds')
            ;
            Dir::create($cache_dir);
        } else {
            $cache_dir =
                $config->data('framework.dir.temp') .
                $config->data(Config::POSIX_ID) .
                $config->data('ds') .
                $config->data('dictionary.compile') .
                $config->data('ds');
            Dir::create($cache_dir);
        }
        $this->cache_dir($cache_dir);
        $use_this = $config->data('parse.read.object.use_this');
        if(is_bool($use_this)){
            $this->useThis($use_this);
        } else {
            $this->useThis(false);
        }
    }

    public function useThis($useThis=null){
        if($useThis !== null){
            $this->use_this = $useThis;
        }
        return $this->use_this;
    }

    public function object(App $object=null){
        if($object !== null){
            $this->setObject($object);
        }
        return $this->getObject();
    }

    private function setObject(App $object=null){
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

    public function local($depth=0, $local=null){
        if($this->local === null){
            $this->local = [];
        }
        if($local !== null){
            $this->local[$depth] = $local;
        }
        if(
            $depth !== null &&
            array_key_exists($depth, $this->local)
        ){
            return clone $this->local[$depth];
        }
        return null;
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

    private static function replace_raw($string=''): string
    {
        $explode = explode('"{{raw|', $string, 2);
        if(array_key_exists(1, $explode)){
            $temp = explode('}}"', $explode[1], 2);
            if(array_key_exists(1, $temp)){
                $explode[1] = implode('}}', $temp);
                $string = implode('{{', $explode);
                return Parse::replace_raw($string);
            }
        }
        $explode = explode('"{{ raw |', $string, 2);
        if(array_key_exists(1, $explode)){
            $temp = explode('}}"', $explode[1], 2);
            if(array_key_exists(1, $temp)){
                $explode[1] = implode('}}', $temp);
                $string = implode('{{', $explode);
                return Parse::replace_raw($string);
            }
        }
        return $string;
    }

    public static function unset(stdClass $object, stdClass $unset): stdClass
    {
        foreach($object as $key => $value){
            if(is_object($value)){
                Parse::unset($value, $unset);
            }
        }
        foreach($unset as $unset_key => $unset_value){
            unset($object->{$unset_value});
        }
        return $object;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws Exception
     */
    public function compile($string='', $data=[], $storage=null, $depth=null, $is_debug=false){
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
                $string[$key] = $this->compile($value, $storage->data(), $storage, $depth, $is_debug);
            }
        }
        elseif(is_object($string)){
            $reserved_keys = [];
            if($this->useThis() === true){
                $source = $storage->data('r3m.io.parse.view.source');
                if(empty($source)){
                    $file = $storage->data('r3m.io.parse.view.url');
                } else {
                    $file = $storage->data('r3m.io.parse.view.source.url');
                }
                if($this->key){
                    $key = $this->object()->config('parse.read.object.this.key');
                    $string->{$key} = $this->key;
//                    $storage->data($key, $this->key);
                }
                if($depth === null){
                    $depth = 0;
                    $key = $this->object()->config('parse.read.object.this.url');
                    $string->{$key} = $file;
                    $this->local($depth, $string);
                } else {
                    $depth++;
                    $this->local($depth, $string);
                }
                foreach($this->object()->config('parse.read.object.this') as $key => $value){
                    $reserved_keys[] = $value;
                }
            }
            foreach($string as $key => $value){
                if(
                    $this->useThis() === true &&
                    in_array(
                        $key,
                        $reserved_keys
                    )
                ){
                    continue;
                }
                try {
                    $this->key = $key;
                    $attribute = $this->object()->config('parse.read.object.this.attribute');
                    $string->{$attribute} = $key;
                    $value = $this->compile($value, $storage->data(), $storage, $depth, $is_debug);
                    $string->$key = $value;
                } catch (Exception | ParseError $exception){
                    ddd($exception);
                }
            }
            //must read into it, copy should be configurable
            $copy = $this->object()->config('parse.read.object.copy');
            if($copy && is_object($copy)){
                foreach($copy as $key => $value){
                    if(property_exists($string, $key)){
                        $string->$value = $string->$key;
                    }
                }
            }
            if($depth === 0){
                $unset = $this->object()->config('parse.read.object.this');
                if($unset && is_object($unset)) {
                    $string = Parse::unset($string, $unset);
                }
            }
            return $string;
        }
        elseif(stristr($string, '{') === false){
            return $string;
        } else {
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
            $string = str_replace('{{ literal }}', '{literal}', $string);
            $string = str_replace('{{literal}}', '{literal}', $string);
            $string = str_replace('{{ /literal }}', '{/literal}', $string);
            $string = str_replace('{{/literal}}', '{/literal}', $string);
            $storage->data('r3m.io.parse.compile.url', $url);
            if($this->useThis() === true){
                $storage->data('this', $this->local($depth));
                $rootNode = $this->local(0);
                if($rootNode && is_object($rootNode)){
                    $attribute = 'this.' . $this->object()->config('parse.read.object.this.rootNode');
                    $storage->data($attribute, $rootNode);
                    $key = 'this';
                    for($index = $depth - 1; $index >= 0; $index--){
                        $key .= '.' . $this->object()->config('parse.read.object.this.parentNode');
                        $storage->data($key, $this->local($index));
                    }
                }
            }
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
            if(empty($this->halt_literal())){
                $string = literal::apply($storage, $string);
            }
            $string = Parse::replace_raw($string);
            $string = str_replace('{{ R3M }}', '{R3M}', $string);
            $string = str_replace('{{R3M}}', '{R3M}', $string);
            $explode = explode('{R3M}', $string, 2);
            if(array_key_exists(1, $explode)){
                if($storage->get('ldelim') === null){
                    $storage->set('ldelim','{');
                }
                if($storage->get('rdelim') === null){
                    $storage->set('rdelim','}');
                }
                $uuid = Core::uuid();
                $storage->data('r3m.io.parse.compile.remove_newline', true);
                $string = str_replace(
                    [
                        '{',
                        '}',
                    ],
                    [
                        '[$ldelim-' . $uuid . ']',
                        '[$rdelim-' . $uuid . ']',
                    ],
                    $explode[1]
                );
                $string = str_replace(
                    [
                        '[$ldelim-' . $uuid . ']',
                        '[$rdelim-' . $uuid . ']',
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
            $id = posix_geteuid();
            $class = $build->storage()->data('namespace') . '\\' . $build->storage()->data('class');
            $exists = class_exists($class);
            if($exists){
                $template = new $class(new Parse($this->object()), $storage);
                $string = $template->run();
                if(empty($this->halt_literal())){
                    $string = Literal::restore($storage, $string);
                }
                if($this->useThis() === true){
                    $storage->data('delete', 'this');
                }
            } else {
                d($write);
                d($string);
                d($build);
                //add phpstan error report on class in /tmp/r3m/io/parse/error/...
                throw new Exception('Class ('. $class .') doesn\'t exist');

            }
        }
        if($string === 'null'){
            return null;
        }
        elseif($string === 'true'){
            return true;
        }
        elseif($string === 'false'){
            return false;
        }
        elseif(is_numeric($string)){
            return $string + 0;
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