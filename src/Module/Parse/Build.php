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

namespace R3m\Io\Module\Parse;



use R3m\Io\Module\SharedMemory;
use stdClass;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Autoload;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Event;
use R3m\Io\Module\Server;

use Exception;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\PluginNotFoundException;
use R3m\Io\Exception\PluginNotAllowedException;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\FileAppendException;
use R3m\Io\Exception\FileMoveException;

class Build {
    const NAME = 'Build';

    const VARIABLE_ASSIGN = 'variable-assign';
    const VARIABLE_DEFINE = 'variable-define';
    const METHOD = 'method';
    const METHOD_CONTROL = 'method-control';

    const METHOD_DEFAULT = [
        'if',
        'else.if',
        'elseif',
        'for',
        'for.each',
        'foreach',
        'while',
        'switch',
        'break',
        'continue',
    ];

    const CODE = 'code';
    const ELSE = 'else';
    const TAG_CLOSE = 'tag-close';
    const DOC_COMMENT = 'doc-comment';

    public $indent;
    private $object;
    private $parse;
    private $storage;
    private $limit;
    private $cache_dir;
    private $is_debug;

    /**
     * @throws Exception
     */
    public function __construct(App $object=null, Parse $parse=null, $is_debug=false){
        $this->is_debug = $is_debug;
        $this->object($object);
        $this->parse($parse);
        $config = $this->object()->data(App::CONFIG);
        if(empty($config)){
            throw new Exception('Config not found in object');
        }
        $this->storage(new Data());
        $this->storage()->data('time.start', microtime(true));
        $this->storage()->data('placeholder.generation.time', '// R3M-IO-' . Core::uuid());
        $this->storage()->data('placeholder.run', '// R3M-IO-' . Core::uuid());
        $this->storage()->data('placeholder.function', '// R3M-IO-' . Core::uuid());
        $this->storage()->data('placeholder.trait', '// R3M-IO-' . Core::uuid());
        $this->storage()->data('placeholder.traituse', '// R3M-IO-' . Core::uuid());
        if(
            is_array($config->data('parse.use')) ||
            is_object($config->data('parse.use'))
        ){
            foreach($config->data('parse.use') as $usage){
                $this->storage()->data('use.' . $usage, new stdClass());
            }
        }
        $debug_url = $this->object()->data('controller.dir.data') . 'Debug.info';
        $this->storage()->data('debug.url', $debug_url);
        $dir_plugin = $config->data(Config::DATA_PARSE_DIR_PLUGIN);
        /*
        ddd($dir_plugin);
        $dir_plugin = [];
        if(empty($dir_plugin)){
            $dir_plugin = [];
            $dir_plugin[] = $config->data('controller.dir.plugin');
            $dir_plugin[] = $config->data('host.dir.plugin');
            $dir_plugin[] = $config->data('host.dir.plugin-2');
            $dir_plugin[] = $config->data('project.dir.plugin');
            $dir_plugin[] = $config->data('framework.dir.plugin');
        }
        */
        $this->storage()->data('plugin', $dir_plugin);
    }

    /**
     * @throws Exception
     */
    public function create($type='', $tree=[], $document=[], $options=[]){
        switch($type){
            case 'header' :
                return $this->createHeader($document);
            case 'require' :
                return $this->createRequire($document);
            case 'use' :
                return $this->createUse($document);
            case 'run' :
                return $this->createRun($document);
            case 'class' :
                return $this->createClass($document);
            case 'trait' :
                return $this->createTrait($document);
            default:
                throw new Exception('Undefined create in build');
        }
    }

    /**
     * @throws Exception
     */
    public function indent($indent=null): string
    {
        if($indent !== null){
            if($indent < 0){
                $indent = 1;
//                throw new Exception('Indentation error: ' . $indent);
            }
            $this->indent = $indent;
        }
        return str_repeat("\t", $this->indent);
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

    private function createClass($document=[]): array
    {
        $config = $this->object()->data(App::CONFIG);

        $storage = $this->storage();
        $key = $storage->data('key');
        //$class = $config->data('dictionary.template') . '_' . $key;
        $class = $this->storage()->data('class');
        $document[] = $this->indent(0) . 'class ' . $class . ' extends Main {';
        $document[] = '';
        $document[] = $this->indent(0) . $storage->data('placeholder.traituse');
        $document[] = '';
        $document[] = $this->indent(1) . 'public function run(){';
        $document[] = $this->indent(2) . 'ob_start();';
        $document[] = $this->indent(0) . $storage->data('placeholder.run');
        $document[] = $this->indent(2) . 'return ob_get_clean();';
        $document[] = $this->indent(1) . '}';
        $document[] = '';
        $document[] = $this->indent(0) . $storage->data('placeholder.function');
        $document[] = $this->indent(0) . '}';
        $document[] = '';
        $document[] = '/**';
        $document[] = ' * Traits' ;
        $document[] = ' */';
        $document[] = '';
        $document[] = $this->storage()->data('placeholder.trait');
        return $document;
    }

    private function createTrait($document=[]): array
    {
        $storage = $this->storage();
        $trait = [];
        $use= [];
        $list = $storage->get('trait');
        if(
            is_array($list)
        ){
            foreach($list as $nr => $record){
                if(
                    array_key_exists('namespace', $record) &&
                    array_key_exists('name', $record) &&
                    array_key_exists('value', $record) &&
                    empty($record['namespace']) &&
                    !empty($record['name'])
                ){
                    $name = str_replace('.', '_', $record['name']);
                    $name.= rand(1000,9999) . rand(1000,9999);
                    $trait[] = 'trait ' . $name . ' {';
                    $use[] = $this->indent(1) . 'use ' . $name . ';';
                    $explode = explode(PHP_EOL, $record['value']);
                    foreach($explode as $line){
                        $trait[] = $this->indent(1) . $line;
                    }
                    $trait[] = '}';
                    $trait[] = '';
                }
                else if(
                    array_key_exists('namespace', $record) &&
                    array_key_exists('name', $record) &&
                    array_key_exists('value', $record) &&
                    !empty($record['namespace']) &&
                    !empty($record['name'])
                ){
                    $name = str_replace('.', '_', $record['name']);
                    $name.= rand(1000,9999) . rand(1000,9999);
                    $namespace = str_replace('.', '\\', $record['namespace']);
                    $trait[] = 'namespace ' . $namespace . ';';
                    $trait[] = 'trait ' . $name . ' {';
                    if(substr($namespace, -1 ,1) !== '\\'){
                        $namespace .= '\\';
                    }
                    $use[] = $this->indent(1) . 'use \\' . $namespace . $name . ';';
                    $explode = explode(PHP_EOL, $record['value']);
                    foreach($explode as $line){
                        $trait[] = $this->indent(1) . $line;
                    }
                    $trait[] = '}';
                    $trait[] = '';
                }
            }
        }
        $list = $this->parse()->storage()->get('import.trait');
        if(
            !empty($list) &&
            is_array($list)
        ){
            foreach ($list as $nr => $record){
                $name = str_replace('.', '_', $record['name']);
                $namespace = str_replace('.', '\\', $record['namespace']);
                if(substr($namespace, -1 ,1) !== '\\'){
                    $namespace .= '\\';
                }
                $use[] = $this->indent(1) . 'use \\' . $namespace . $name . ';';
            }
        }
        $traits = implode("\n", $trait);
        $usage = implode("\n", $use);
        $count = 0;
        foreach($document as $nr => $row){
            $document[$nr] = str_replace($storage->get('placeholder.trait'), $traits, $row, $count);
            if($count > 0){
                break;
            }
        }
        $count = 0;
        foreach($document as $nr => $row){
            $document[$nr] = str_replace($storage->get('placeholder.traituse'), $usage, $row, $count);
            if($count > 0){
                break;
            }
        }
        return $document;
    }


    private function createUse($document=[]): array
    {
        $storage = $this->storage();
        $use = [];
        foreach($storage->data('use') as $name => $record){
            $use[] = 'use ' . $name . ';';
        }
        $use[] = '';
        $usage = implode("\n", $use);
        $count = 0;
        foreach($document as $nr => $row){
            $document[$nr] = str_replace($storage->data('placeholder.use'), $usage, $row, $count);
            if($count > 0){
                break;
            }
        }
        return $document;
    }

    private function createRun($document=[]): array
    {
        $storage = $this->storage();
        $run = $storage->data('run');
        $content = implode("\n", $run);
        $count = 0;
        if(is_array($document)){
            foreach($document as $nr => $row){
                $document[$nr] = str_replace($storage->data('placeholder.run'), $content, $row, $count);
                if($count > 0){
                    break;
                }
            }
        }
        return $document;
    }

    /**
     * @throws PluginNotFoundException
     * @throws PluginNotAllowedException
     * @throws Exception
     */
    private function createRequireContent($type='', $document=[]): array
    {
        $object = $this->object();
        $url = false;
        $config = $object->data(App::CONFIG);
        $storage = $this->storage();
        $dir_plugin = $config->get(Config::DATA_PARSE_DIR_PLUGIN);
        if(empty($dir_plugin)){
            $dir_plugin = $storage->data('plugin');
        }
        $data = $storage->data($type);
        if(empty($data)){
            return $document;
        }
        $placeholder = $storage->data('placeholder.function');
        $url_list = [];
        $limit = $this->limit();
        foreach($data as $name => $record){
            $exist = false;
            $function_name = str_replace('function_', '', $name, $function_count);
            if(
                $function_count >= 1 &&
                array_key_exists('method', $record) &&
                array_key_exists('trait', $record['method']) &&
                !empty($record['method']['trait'])
            ){
                //traits goes a different path
                continue;
            }
            $modifier_name = str_replace('modifier_', '', $name, $modifier_count);
            if(
                empty($limit) ||
                (
                    !empty($limit) &&
                    array_key_exists('function', $limit) &&
                    in_array($function_name, $limit['function']) &&
                    $function_count >= 1
                ) ||
                (
                    !empty($limit) &&
                    array_key_exists('modifier', $limit) &&
                    in_array($modifier_name, $limit['modifier']) &&
                    $modifier_count >= 1
                )
            ){
                $indent = $this->indent - 1;
                if(is_array($dir_plugin)){
                    foreach($dir_plugin as $nr => $dir){
                        $file = ucfirst($name) . $config->data('extension.php');
                        $url = $dir . $file;
                        $object->logger($object->config('project.log.error'))->info('Parse: ' . $url);
                        $url_list[] = $url;
                        //add ramdisk
                        $file_read = false;
                        $ramdisk_dir = false;
                        $ramdisk_url = false;
                        $config_dir = false;
                        $config_url = false;
                        $config_mtime = false;
                        $is_ramdisk_url = false;
                        $is_shared_memory = false;
                        if(
                            $object->config('ramdisk.url') &&
                            empty($object->config('ramdisk.is.disabled'))
                        ){
                            $config_dir = $this->object()->config('ramdisk.url') .
                                $this->object()->config(Config::POSIX_ID) .
                                $this->object()->config('ds') .
                                'Plugin' .
                                $this->object()->config('ds')
                            ;
                            $config_url = $config_dir .
                                'File.Mtime' .
                                $this->object()->config('extension.json')
                            ;
                            $config_mtime = $this->object()->data_read($config_url, sha1($config_url));
                            if(!$config_mtime){
                                $config_mtime = new Data();
                            }
                            if(
                                $config_mtime->has(sha1($url)) &&
                                $config_mtime->get(sha1($url)) === File::mtime($url)
                            ) {
                                $file_read = SharedMemory::read($object, $url);
//                            d($file_read);
                            }
                            /*
                            if(
                                $object->config('cache.parse.plugin.url.directory_length') &&
                                $object->config('cache.parse.plugin.url.directory_separator') &&
                                $object->config('cache.parse.plugin.url.directory_pop_or_shift') &&
                                $object->config('cache.parse.plugin.url.name_length') &&
                                $object->config('cache.parse.plugin.url.name_separator') &&
                                $object->config('cache.parse.plugin.url.name_pop_or_shift')
                            ){
                                $ramdisk_dir = $this->object()->config('ramdisk.url') .
                                    $this->object()->config(Config::POSIX_ID) .
                                    $this->object()->config('ds') .
                                    'Plugin' .
                                    $this->object()->config('ds')
                                ;
                                $ramdisk_file =
                                    Autoload::name_reducer(
                                        $this->object(),
                                        str_replace('/', '_', $dir),
                                        $this->object()->config('cache.parse.plugin.url.directory_length'),
                                        $this->object()->config('cache.parse.plugin.url.directory_separator'),
                                        $this->object()->config('cache.parse.plugin.url.directory_pop_or_shift')
                                    ) .
                                    '_' .
                                    Autoload::name_reducer(
                                        $this->object(),
                                        $file,
                                        $this->object()->config('cache.parse.plugin.url.name_length'),
                                        $this->object()->config('cache.parse.plugin.url.name_separator'),
                                        $this->object()->config('cache.parse.plugin.url.name_pop_or_shift')
                                    );
                                $ramdisk_url = $ramdisk_dir . $ramdisk_file;
                                $config_dir = $this->object()->config('ramdisk.url') .
                                    $this->object()->config(Config::POSIX_ID) .
                                    $this->object()->config('ds') .
                                    'Plugin' .
                                    $this->object()->config('ds')
                                ;
                                $config_url = $config_dir .
                                    'File.Mtime' .
                                    $this->object()->config('extension.json')
                                ;
                                $config_mtime = $this->object()->data_read($config_url, sha1($config_url));
                                if(!$config_mtime){
                                    $config_mtime = new Data();
                                }
                                elseif(
                                    $config_mtime->has(sha1($ramdisk_url)) &&
                                    File::mtime($config_mtime->get(sha1($ramdisk_url))) && File::mtime($ramdisk_url)
                                ){
                                    $is_ramdisk_url = true;
                                    $url = $ramdisk_url;
                                }
                            }
                            */
                        }
                        $file_exist = File::exist($url);
                        if(
                            empty($file_read) &&
                            $file_exist
                        ){
                            $file_read = File::read($url);
                        }
                        elseif($file_exist && is_scalar($file_read)){
                            $is_shared_memory = true;
                        }
                        if(
                            File::exist($url) &&
                            is_scalar($file_read)
                        ){
                            $explode = explode('function', $file_read);
                            $explode[0] = '';
                            $read = implode('function', $explode);
                            $read = explode(PHP_EOL, $read);
                            foreach($read as $read_nr => $row){
                                $read[$read_nr] = $this->indent($indent) . $row;
                            }
                            $read = implode(PHP_EOL, $read);
                            $read .= PHP_EOL;
                            $document = str_replace($placeholder, $read . $placeholder, $document);
                            $exist = true;
                            if(
                                $is_shared_memory === false &&
                                $object->config('ramdisk.url') &&
                                empty($object->config('ramdisk.is.disabled'))
                            ){
                                SharedMemory::write($object, $url, File::read($url));
                                $config_mtime->set(sha1($url), File::mtime($url));
                                $config_is_write = $config_mtime->write($config_url);
                                exec('chmod 640 ' . $config_url);
                            }
                            /*
                            if(
                                $is_ramdisk_url === false &&
                                $ramdisk_dir &&
                                $ramdisk_url &&
                                $config_dir &&
                                $config_url &&
                                $config_mtime
                            ){

                                Dir::create($ramdisk_dir);
                                File::put($ramdisk_url, $file_read);
                                $config_mtime->set(sha1($ramdisk_url), $url);
                                $config_mtime->write($config_url);
                                exec('chmod 640 ' . $ramdisk_url);
                                exec('chmod 640 ' . $config_url);

                            }
                            */
                            Event::trigger($object, 'parse.build.plugin.require', [
                                'url' => $url,
                                'name' => $name
                            ]);
                            break;
                        }
                    }
                } else {
                    $debug = debug_backtrace(true);
                    ddd($debug);
                    throw new Exception('Configure parse.dir.plugin');
                }
                if($exist === false){
                    $text = $name . ' near ' . $record['value'] . ' on line: ' . $record['row'] . ' column: ' . $record['column'] . ' in: ' . $storage->data('source');
                    $exception = new PluginNotFoundException('Function not found: ' . $text, $dir_plugin);
                    Event::trigger($object, 'parse.build.plugin.not_found', [
                        'url' => $url,
                        'name' => $name,
                        'exception' => $exception
                    ]);
                    throw $exception;
                }
            } elseif(array_key_exists('function', $limit)) {
                $exception = new PluginNotAllowedException('Function (' . $name . ') not allowed, allowed: ' . implode(',', $limit['function']));
                Event::trigger($object, 'parse.build.plugin.not_allowed', [
                    'url' => $url,
                    'name' => $name,
                    'exception' => $exception,
                    'allowed' => $limit['function']
                ]);
                throw $exception;
            }
        }
        return $document;
    }

    private function createRequireCategory($type='', $document=[]): array
    {
        $config = $this->object()->data(App::CONFIG);
        $storage = $this->storage();
        $dir_plugin = $storage->data('plugin');
        $data = $storage->data($type);
        if(empty($data)){
            return $document;
        }
        foreach($data as $name => $record){
            $file = ucfirst($name) . $config->data('extension.php');
            foreach($dir_plugin as $nr => $dir){
                if($nr < 1){
                    $if_elseif = 'if';
                } else {
                    $if_elseif = 'elseif';
                }
                $url = $dir . $file;
                $document[] = $if_elseif . ' (File::exist(\'' . $url . '\')){';
                $document[] = "\t" . 'require_once \''. $url .'\';';
                $document[] = '}';
            }
            $document[] = 'else';
            $document[] = '{';
            $document[] = "\t" . 'throw new Exception(\'Plugin not found: ./Plugin/' . $file . '\');';
            $document[] = '}';
        }
        return $document;
    }

    /**
     * @throws FileWriteException
     * @throws FileAppendException
     * @throws FileMoveException
     * @throws ObjectException
     */
    public function write($url, $document=[], $string=''): string
    {
        $write = implode("\n", $document);
        $this->storage()->data('time.end', microtime(true));
        $this->storage()->data('time.duration', $this->storage()->data('time.end') - $this->storage()->data('time.start'));
        $write = str_replace($this->storage()->data('placeholder.generation.time'), round($this->storage()->data('time.duration') * 1000, 2). ' msec', $write);
        $dir = Dir::name($url, Dir::CHMOD);
        Dir::create($dir);
        File::put($url, $write);
        exec('chmod 640 ' . $url);
        $object = $this->object();
        Event::trigger($object, 'parse.build.write', [
            'url' => $url,
            'string' => $string,
            'storage' => $this->storage(),
        ]);
        return $write;
    }

    public static function getPluginMultiline(App $object){
        return $object->config('parse.plugin.multi_line');
    }

    /**
     * @throws Exception
     */
    public function document(Data $data, $tree=[], $document=[]): array
    {
        $object = $this->object();
        $is_tag = false;
        $tag = null;
        $this->indent(2);
        $counter = 0;
        $storage = $this->storage();
        if(!empty($data->data('is.debug'))){
            $is_debug = $data->data('is.debug');
            $storage->data('is.debug', $data->data('is.debug'));
        }
        $run = $storage->data('run');
        if(empty($run)){
            $run = [];
        }
        $type = null;
        $select = null;
        $selection = [];
        $skip_nr = null;
        $is_control = false;
        $remove_newline = false;
        foreach($tree as $nr => $record){
            $start = microtime(true);
            if(
                $skip_nr !== null &&
                $nr > $skip_nr
            ){
                $skip_nr = null;
            }
            elseif($skip_nr !== null){
                continue;
            }
            if(
                $is_tag === false &&
                !in_array(
                    $record['type'],
                    Token::NOT_TYPE_ECHO
                )
            ){
                if($remove_newline && $data->data('r3m.io.parse.compile.remove_newline') !== false){
                    $explode = explode("\n", $record['value'], 2);
                    if(count($explode) == 2){
                        $temp = trim($explode[0]);
                        if(empty($temp)){
                            $record['value'] = $explode[1];
                        }
                    }
                    $remove_newline = false;
                }
                $run[] = $this->indent() .
                    'echo \'' .
                    str_replace(
                        [
                            '\\',
                            '\'',
                        ],
                        [
                            '\\\\',
                            '\\\'',
                        ],
                        $record['value']
                    ) .
                    '\';';
            }
            elseif(
                $is_tag === false &&
                $record['type'] == Token::TYPE_QUOTE_DOUBLE_STRING
            ){
                $run[] =  $this->indent() . '$string = \'' . str_replace('\'', '\\\'', substr($record['value'], 1, -1)). '\';';
                $run[] =  $this->indent() . '$string = $this->parse()->compile($string, [], $this->storage());';
                $run[] =  $this->indent() .  'echo \'"\' . $string . \'"\';';
            }
            elseif($record['type'] == Token::TYPE_CURLY_OPEN){
                $is_tag = true;
                continue;
            }
            elseif($record['type'] == Token::TYPE_DOC_COMMENT){
                $run[] = $this->indent() . 'echo \'' . str_replace('\'', '\\\'', $record['value']) . '\';';
                $run[] = '';
            }
            elseif($record['type'] == Token::TYPE_CURLY_CLOSE){
                switch($type){
                    case Token::TYPE_STRING :
                        if($select['value'] == 'if'){
                            throw new Exception('if must be a method, use {if()} on line: ' . $select['row'] . ', column: ' .  $select['column']  . ' in: ' .  $data->data('r3m.io.parse.view.url') );
                        } else {
                            throw new Exception('Possible variable sign or method missing (), on line: ' . $select['row'] . ', column: ' .  $select['column']  . ' in: ' .  $data->data('r3m.io.parse.view.url'));
                        }
                    case Token::TYPE_IS_MINUS_MINUS :
                    case Token::TYPE_IS_PLUS_PLUS :
                        $selection = Variable::is_count($this, $storage, $selection);
                        $run[] = $this->indent() . '$this->parse()->is_assign(true);';
                        $run[] = $this->indent() . Variable::count_assign($this, $storage, $selection, false) . ';';
                        $run[] = $this->indent() . '$this->parse()->is_assign(false);';
                        $remove_newline = true;
                        break;
                    case Build::VARIABLE_ASSIGN :
                        $run[] = $this->indent() . '$this->parse()->is_assign(true);';
                        $run[] = $this->indent() . Variable::assign($this, $storage, $selection, false) . ';';
                        $run[] = $this->indent() . '$this->parse()->is_assign(false);';
                        $remove_newline = true;
                        break;
                    case Build::VARIABLE_DEFINE :
                        $extra = '';
                        $define = Variable::define($this, $storage, $selection, $extra);
                        if($extra){
                            $run[] = $this->indent() . $extra . PHP_EOL;
                        }
                        $run[] = $this->indent() . '$variable = ' . $define . ';';
                        $run[] = $this->indent() . 'if (is_object($variable)){ return $variable; }';
                        $run[] = $this->indent() . 'elseif (is_array($variable)){ return $variable; }';
                        $run[] = $this->indent() . 'else { echo $variable; } ';
                        $remove_newline = true;
                        break;
                    case Build::METHOD :
                        $run[] = $this->indent() . '$method = ' . Method::create($this, $storage, $selection) . ';';
                        $run[] = $this->indent() . 'if (is_object($method)){ return $method; }';
                        $run[] = $this->indent() . 'elseif (is_array($method)){ return $method; }';
                        $run[] = $this->indent() . 'else { echo $method; }';
                        $remove_newline = true;
                        break;
                    case Build::METHOD_CONTROL :
                        $multi_line = Build::getPluginMultiline($this->object());
                        if(
                            in_array(
                                $select['method']['name'],
                                $multi_line
                            //capture.append
                            )
                        ){
                            $selection = Method::capture_selection($this, $storage, $tree, $selection);
                            if($select['method']['name'] === 'trait'){
                                $trait = Method::create_trait($this, $storage, $selection);
                                $list = $storage->get('trait');
                                if(empty($list)){
                                    $list = [];
                                }
                                $is_found = false;
                                foreach($list as $list_nr => $list_value){
                                    if(
                                        $list_value['trait'] === $trait['trait'] &&
                                        $list_value['namespace'] === $trait['namespace']
                                    ){
                                        $is_found = true;
                                        break;
                                    }
                                }
                                if(!$is_found){
                                    $list[] = $trait;
                                    $storage->set('trait', $list);
                                }
                            } else {
                                $run[] = $this->indent() . Method::create_capture($this, $storage, $selection) . ';';
                            }
                            foreach($selection as $skip_nr => $item){
                                //need skip_nr
                            }
                            $remove_newline = true;
                        } else {
                            $control = Method::create_control($this, $storage, $selection);
                            $explode = explode(' ', $control, 2);
                            if(
                                in_array(
                                    $explode[0],
                                    [
                                        'break',
                                        'continue'
                                    ]
                                )
                            ){
                                $run[] = $this->indent() . $control . ';';
                            }
                            elseif(
                                array_key_exists('method', $select) &&
                                $select['method']['php_name'] == Token::TYPE_FOREACH
                            ){
                                $run[] = $this->indent() . $control;
                                $this->indent($this->indent+1);
                            }
                            else {
                                $run[] = $this->indent() . $control . ' {';
                                $this->indent($this->indent+1);
                                $is_control = true;
                            }
                            $control = null;
                            $remove_newline = true;
                        }
                        break;
                    case Build::ELSE :
                        $this->indent($this->indent-1);
                        $run[] = $this->indent() . '} else {';
                        $this->indent($this->indent+1);
                        $remove_newline = true;
                        break;
                    case Build::TAG_CLOSE :
                        $multi_line = Build::getPluginMultiline($this->object());
                        foreach($multi_line as $multi_line_nr => $plugin){
                            $multi_line[$multi_line_nr] = '/' . $plugin;
                        }
                        if(
                            !in_array(
                                $select['tag']['name'],
                                $multi_line
                            //'/capture.append'
                            )
                        ){
                            $this->indent($this->indent-1);
                            $run[] = $this->indent() . '}';
                        }
                        $remove_newline = true;
                        break;
                    case Build::DOC_COMMENT :
//                      $run[] = $this->indent() .
                        /*
                        if($type !== null){
                            throw new Exception('type (' . $type . ') undefined');
                        }
                        */
                        break;
                    default:
                        if($type !== null){
                            d($selection);
                            throw new Exception('type (' . $type . ') value (' . $select['value'] . ')undefined in source: ' . $this->storage()->data('source') . ' on line: ' . $record['row']);
                        }
                }
                $is_tag = false;
                $selection = [];
                $type = null;
            }
            if($is_tag !== false){
                if($type === null){
                    $type = Build::getType($this->object(), $record);
                    $select = $record;
                }
                $selection[$nr] = $record;
            }
        }
        $storage->data('run', $run);
        return $document;
    }

    /**
     * @throws Exception
     */
    public static function getType($object='', $record=[]): string
    {
        switch($record['type']){
            case Token::TYPE_VARIABLE :
                if(
                    array_key_exists('variable', $record) &&
                    $record['variable']['is_assign'] === true
                ){
                    return Build::VARIABLE_ASSIGN;
                } else {
                    return Build::VARIABLE_DEFINE;
                }
            case Token::TYPE_METHOD :
                $multi_line = Build::getPluginMultiline($object);
                // 'capture_append'
                foreach($multi_line as $nr => $plugin){
                    $multi_line[$nr] = 'function_' . str_replace('.', '_', $plugin);
                }
                $method = Build::METHOD_DEFAULT;
                $method = array_merge($method, $multi_line);
                if(
                    in_array(
                        $record['method']['php_name'],
                        $method
                    )
                ){
                    return Build::METHOD_CONTROL;
                } else {
                    return Build::METHOD;
                }
            case Token::TYPE_TAG_CLOSE :
                return Build::TAG_CLOSE;
            case Token::TYPE_STRING :
                if(
                    in_array(
                        $record['value'],
                        [
                            'else'
                        ]
                    )
                ){
                    return Build::ELSE;
                }
                return Token::TYPE_STRING;
            case Token::TYPE_QUOTE_DOUBLE_STRING :
                return Token::TYPE_QUOTE_DOUBLE_STRING;
            case Token::TYPE_CURLY_CLOSE :
                return Token::TYPE_CURLY_CLOSE;
            case Token::TYPE_AMPERSAND :
                return Token::TYPE_AMPERSAND;
            case Token::TYPE_IS_DIVIDE :
                return Token::TYPE_IS_DIVIDE;
            case Token::TYPE_IS_PLUS_PLUS :
                return Token::TYPE_IS_PLUS_PLUS;
            case Token::TYPE_IS_MINUS_MINUS :
                return Token::TYPE_IS_MINUS_MINUS;
            case Token::TYPE_DOC_COMMENT :
                return Token::TYPE_DOC_COMMENT;
            case Token::TYPE_HEX :
                return Token::TYPE_HEX;
            default:
                throw new Exception('Undefined type (' . $record['type'] . ')');
        }
    }

    /**
     * @throws PluginNotAllowedException
     * @throws PluginNotFoundException
     */
    private function createRequire($document=[]): array
    {
        $document = $this->createRequireContent('modifier', $document);
        $document = $this->createRequireContent('function', $document);
        $document = str_replace('function ' . 'capture', 'private function ' . 'capture', $document);
        $document = str_replace('function ' . 'modifier', 'private function ' . 'modifier', $document);
        $document = str_replace('function ' . 'function', 'private function ' . 'function', $document);
        $this->storage()->data('document', $document);
        return $document;
    }

    private function createHeader($document=[]): array
    {
        if(empty($document)){
            $document = [];
        }
        $config = $this->object()->data(App::CONFIG);
        $namespace = $this->storage()->data('namespace');
        $document[] = '<?php';
        $document[] = '/**';
        $document[] = ' * @copyright                (c) Remco van der Velde 2019 - ' . date('Y');
        $document[] = ' * @version                  ' . $config->data('framework.version');
        $document[] = ' * @license                  MIT';
        $document[] = ' * @note                     Auto generated file, do not modify!';
        $document[] = ' * @author                   R3m\Io\Module\Parse\Build';
        $document[] = ' * @author                   Remco van der Velde remco@universeorange.com';
        if($this->storage()->data('parent')){
            $document[] = ' * @parent                   ' . $this->storage()->data('parent');
        }
        $document[] = ' * @source                   ' . $this->storage()->data('source');
        $document[] = ' * @generation-date          ' . date('Y-m-d H:i:s');
        $document[] = ' * @generation-time          ' . $this->storage()->data('placeholder.generation.time');
        $document[] = ' */';
        $document[] = '';
        $document[] = 'namespace ' . $namespace . ';';
        $document[] = '';
        $document[] = $this->storage()->data('placeholder.use');
        $this->storage()->data('document', $document);
        return $document;
    }

    public function meta($options=[]): array
    {
        $config = $this->object()->data(App::CONFIG);
        $this->storage()->data('placeholder.use', '// R3M-IO-' . Core::uuid());
        $namespace = 'R3m\\Io\\Module\\' .  $config->data('dictionary.compile');
        $this->storage()->data('namespace', $namespace);
        $key = $this->storage()->data('key');
        $name = '';
        if(isset($options['parent'])){
            $name .= str_replace(
                    [
                        '.',
                        '-',
                    ],
                    [
                        '_',
                        '_'
                    ],
                    basename($options['parent'])
                ) . '_';
        }
        if(isset($options['source'])){
            $name .= str_replace(
                    [
                        '.',
                        '-'
                    ],
                    [
                        '_',
                        '_'
                    ],
                    basename($options['source'])
                ) . '_';
        }
        $name = str_replace('_tpl', '', $name);
        $class = $config->data('dictionary.template') . '_' . $name . $key;
        $this->storage()->data('class', $class);
        $meta = [
            'namespace' => $namespace,
            'class' => $class
        ];
        return $meta;
    }

    public function object($object=null){
        if($object !== null){
            $this->setObject($object);
        }
        return $this->getObject();
    }

    private function setObject($object=null){
        $this->object = $object;
    }

    private function getObject(){
        return $this->object;
    }

    public function parse($parse=null){
        if($parse !== null){
            $this->setParse($parse);
        }
        return $this->getParse();
    }

    private function setParse($parse=null){
        $this->parse = $parse;
    }

    private function getParse(){
        return $this->parse;
    }

    public function storage($object=null){
        if($object !== null){
            $this->setStorage($object);
        }
        return $this->getStorage();
    }

    private function setStorage($object=null){
        $this->storage = $object;
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

    /**
     * @throws Exception
     */
    public function url($string=null, $options=[]): string
    {
        $object = $this->object();
        $storage = $this->storage();
        $url = $storage->data('url');
        if($string !== null && $url === null){
            $key = sha1($string);
            $config = $this->object()->data(App::CONFIG);
            $dir = $this->cache_dir();
            if(empty($dir)){
                throw new Exception('Cache dir empty in Build');
            }
            $autoload = $this->object()->data(App::NAMESPACE . '.' . Autoload::NAME . '.' . App::R3M);
            if($autoload) {
                $prefixList = $autoload->getPrefixList();
                $autoload->unregister();
                $autoload->addPrefix($config->data('dictionary.compile'),  $dir);
                foreach ($prefixList as $nr => $record){
                    if(
                        array_key_exists('prefix', $record) &&
                        array_key_exists('directory', $record) &&
                        array_key_exists('extension', $record)
                    ){
                        $autoload->addPrefix($record['prefix'],  $record['directory'], $record['extension']);
                    }
                    else if(
                        array_key_exists('prefix', $record) &&
                        array_key_exists('directory', $record)
                    ){
                        $autoload->addPrefix($record['prefix'],  $record['directory']);
                    }
                }
                $autoload->register();
            }
            $name = '';
            if(isset($options['parent'])){
                $name .= str_replace(
                        [
                            '.',
                            '-'
                        ],
                        [
                            '_',
                            '_'
                        ],
                        basename($options['parent'])
                    ) . '_';
            }
            if(isset($options['source'])){
                $name .= str_replace(
                        [
                            '.',
                            '-'
                        ],
                        [
                            '_',
                            '_'
                        ],
                        basename($options['source'])) . '_';
            }
            $name = str_replace('_tpl', '', $name);
            if(
                $object->config('cache.parse.url.name_length') &&
                $object->config('cache.parse.url.name_separator') &&
                $object->config('cache.parse.url.name_pop_or_shift')
            ){
                $name = Autoload::name_reducer(
                    $this->object(),
                    $name,
                    $object->config('cache.parse.url.name_length'),
                    $object->config('cache.parse.url.name_separator'),
                    $object->config('cache.parse.url.name_pop_or_shift')
                );
            }
            $url =
                $dir .
                $config->data('dictionary.template') .
                '_' .
                $name .
                $key .
                $config->data('extension.php')
            ;
            $storage->data('url', $url);
            $storage->data('key', $key);
            if(!empty($options['parent'])){
                $storage->data('parent', $options['parent']);
            }
            if(!empty($options['source'])){
                $storage->data('source', $options['source']);
            }
            $this->meta($options);
        }
        return $url;
    }

    /**
     * @throws Exception
     */
    public function require($type='', $tree=[]): array
    {
        switch($type){
            case 'function':
                $tree = $this->requireFunction($tree);
                break;
            case 'modifier':
                $tree = $this->requireModifier($tree);
                break;
            default:
                throw new Exception('Add type not defined');
        }
        return $tree;
    }

    private function requireModifier($tree=[]): array
    {
        $storage = $this->storage();
        foreach($tree as $nr => $record){
            if(
                $record['type'] == Token::TYPE_VARIABLE &&
                array_key_exists('variable', $record) &&
                array_key_exists('has_modifier', $record['variable'])
            ){
                foreach($record['variable']['modifier'] as $modifier_list_nr => $modifier_list){
                    foreach($modifier_list as $modifier_nr => $modifier){
                        if(
                            array_key_exists('type', $modifier) &&
                            $modifier['type'] == Token::TYPE_MODIFIER
                        ){
                            $name = 'modifier_' . str_replace('.', '_', $modifier['value']);
                            $tree[$nr]['variable']['modifier'][$modifier_list_nr][$modifier_nr]['php_name'] = $name;
                            $storage->data('modifier.' . $name, $record);
                        }
                    }
                }
            }
        }
        return $tree;
    }

    private function requireFunction($tree=[]): array
    {
        $storage = $this->storage();
        foreach($tree as $nr => $record){
            if($record['type'] == Token::TYPE_METHOD){
                $multi_line = Build::getPluginMultiline($this->object());
                // 'capture.append'
                $method = Build::METHOD_DEFAULT;
                $method = array_merge($method, $multi_line);
                if(
                    !in_array(
                        $record['method']['name'],
                        $method
                    )
                ){
                    $name = 'function_' . str_replace('.', '_', $record['method']['name']);
                    $storage->data('function.' . $name, $record);
                } else {
                    $multi_line = Build::getPluginMultiline($this->object());
                    // 'capture.append'
                    if(
                        in_array(
                            $record['method']['name'],
                            $multi_line
                        )
                    ){
                        $name = 'function_' . str_replace('.', '_', $record['method']['name']);
                        $storage->data('function.' . $name, $record);
                    } else {
                        $name = str_replace('.', '', $record['method']['name']);
                    }
                }
                $tree[$nr]['method']['php_name'] = $name;
            }
        }
        return $tree;
    }
}