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

use stdClass;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Autoload;
use R3m\Io\Module\Parse;

use Exception;
use R3m\Io\Exception\PluginNotFoundException;


class Build {
    const NAME = 'Build';

    const VARIABLE_ASSIGN = 'variable-assign';
    const VARIABLE_DEFINE = 'variable-define';
    const METHOD = 'method';
    const METHOD_CONTROL = 'method-control';
    const CODE = 'code';
    const ELSE = 'else';
    const TAG_CLOSE = 'tag-close';
    const DOC_COMMENT = 'doc-comment';

    public $indent;
    private $object;
    private $parse;
    private $storage;
    private $cache_dir;
    private $is_debug;

    /**
     * @throws Exception
     */
    public function __construct($object=null, $parse=null, $is_debug=false){
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

        if(
            is_array($config->data('parse.use')) ||
            is_object($config->data('parse.use'))
        ){
            foreach($config->data('parse.use') as $usage){
                $this->storage()->data('use.' . $usage, new stdClass());
            }
        }
        /*
        $this->storage()->data('use.stdClass', new stdClass());
        $this->storage()->data('use.R3m\\Io\\App', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Config', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Cli', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Core', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Data', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Dir', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\File', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Filter', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Handler', new stdClass());        
        $this->storage()->data('use.R3m\\Io\\Module\\Host', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Parse', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Response', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Route', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Sort', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Validate', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\View', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Template\\Main', new stdClass());
        $this->storage()->data('use.Exception', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Exception\\AuthenticationException', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Exception\\AuthorizationException', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Exception\\ErrorException', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Exception\\FileAppendException', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Exception\\FileMoveException', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Exception\\FileWriteException', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Exception\\LocateException', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Exception\\ObjectException', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Exception\\PluginNotFoundException', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Exception\\UrlEmptyException', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Exception\\UrlNotExistException', new stdClass());
        */
        $debug_url = $this->object()->data('controller.dir.data') . 'Debug.info';
        $this->storage()->data('debug.url', $debug_url);
        $dir_plugin = $config->data(Config::DATA_PARSE_DIR_PLUGIN);
        $dir_plugin = [];
        if(empty($dir_plugin)){
            $dir_plugin = [];
            $dir_plugin[] = $config->data('controller.dir.plugin');
            $dir_plugin[] = $config->data('host.dir.plugin');
            $dir_plugin[] = $config->data('host.dir.plugin-2');
            $dir_plugin[] = $config->data('project.dir.plugin');
            $dir_plugin[] = $config->data('framework.dir.plugin');
        }
        $this->storage()->data('plugin', $dir_plugin);
    }

    /**
     * @throws Exception
     */
    public function create($type='', $tree=[], $document=[], $options=[]){
        switch($type){
            case 'header' :
                return $this->createHeader($document);
            break;
            case 'require' :
                return $this->createRequire($document, $tree);
            break;
            case 'use' :
                return $this->createUse($document);
            break;
            case 'run' :
                return $this->createRun($document);
            break;
            case 'class' :
                return $this->createClass($document);
            break;
            default:
                throw new Exception('Undefined create in build');
        }
    }

    public function indent($indent=null){
        if($indent !== null){
            $this->indent = $indent;
        }
        return str_repeat("\t", $this->indent);
    }

    private function createClass($document=[]){
        $config = $this->object()->data(App::CONFIG);

        $storage = $this->storage();
        $key = $storage->data('key');
        //$class = $config->data('dictionary.template') . '_' . $key;
        $class = $this->storage()->data('class');        
        $document[] = $this->indent(0) . 'class ' . $class . ' extends Main {';
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
        return $document;
    }

    private function createUse($document=[]){
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

    private function createRun($document=[]){
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
     */
    private function createRequireContent($type='', $document=[]){
        $config = $this->object()->data(App::CONFIG);
        $storage = $this->storage();
        $dir_plugin = $storage->data('plugin');
        $data = $storage->data($type);
        if(empty($data)){
            return $document;
        }
        $placeholder = $storage->data('placeholder.function');
        $url_list = [];
        foreach($data as $name => $record){
            $exist = false;
            foreach($dir_plugin as $nr => $dir){
                $file = ucfirst($name) . $config->data('extension.php');
                $url = $dir . $file;
                $url_list[] = $url;
                if(File::exist($url)){
                    $read = File::read($url);
                    $explode = explode('function', $read);
                    $explode[0] = '';
                    $read = implode('function', $explode);
                    $indent = $this->indent - 1;
                    $read = explode("\n", $read);
                    foreach($read as $nr => $row){
                        $read[$nr] = $this->indent($indent) . $row;
                    }
                    $read = implode("\n", $read);
                    $read .= "\n";
                    $this->indent = $this->indent + 1;
                    $document = str_replace($placeholder, $read . $placeholder, $document);
                    $exist = true;
                    break;
                }
            }
            if($exist === false){
                $text = $name . ' near ' . $record['value'] . ' on line: ' . $record['row'] . ' column: ' . $record['column'] . ' in: ' . $storage->data('source');
                if($config->data(Config::DATA_FRAMEWORK_ENVIRONMENT) == Config::MODE_DEVELOPMENT) {
                    Core::cors($this->object());
                    d($dir_plugin);
                    d($url_list);
                }
                throw new PluginNotFoundException('Function not found: ' . $text);
            }
        }
        return $document;
    }


    private function createRequireCategory($type='', $document=[]){
        $config = $this->object()->data(App::CONFIG);
        $storage = $this->storage();
        $dir_plugin = $storage->data('plugin');
        $data = $storage->data($type);
        if(empty($data)){
            return $document;
        }
        foreach($data as $name => $record){
            foreach($dir_plugin as $nr => $dir){
                if($nr < 1){
                    $if_elseif = 'if';
                } else {
                    $if_elseif = 'elseif';
                }
                $file = ucfirst($name) . $config->data('extension.php');
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

    public function write($url, $document=[]){
        $write = implode("\n", $document);
        $this->storage()->data('time.end', microtime(true));
        $this->storage()->data('time.duration', $this->storage()->data('time.end') - $this->storage()->data('time.start'));
        $write = str_replace($this->storage()->data('placeholder.generation.time'), round($this->storage()->data('time.duration') * 1000, 2). ' msec', $write);
        $dir = Dir::name($url);
        $create = Dir::create($dir);
        return File::write($url, $write);
    }

    public static function getPluginMultiline(App $object){
        return $object->config('parse.plugin.multi_line');
    }

    /**
     * @throws Exception
     */
    public function document(Data $data, $tree=[], $document=[]){
        $is_tag = false;
        $tag = null;
        $this->indent(2);
        $counter = 0;
        $storage = $this->storage();
        $is_debug = '';
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
                $record['type'] == Token::TYPE_STRING
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
                            d($select);
                            throw new Exception('Possible variable sign or method missing (), on line: ' . $select['row'] . ', column: ' .  $select['column']  . ' in: ' .  $data->data('r3m.io.parse.view.url') . ' ' . $record['value']);
                        }
                    break;       
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
                        $run[] = $this->indent() . '$variable = ' . Variable::define($this, $storage, $selection) . ';';
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
                            $run[] = $this->indent() . Method::create_capture($this, $storage, $selection) . ';';                    
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
                        foreach($multi_line as $nr => $plugin){
                            $multi_line[$nr] = '/' . $plugin;
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
//                            d($selection);
                            throw new Exception('type (' . $type . ') undefined in source: ' . $this->storage()->data('source'));
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
    public static function getType($object='', $record=[]){
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
            break;
            case Token::TYPE_METHOD :
                $multi_line = Build::getPluginMultiline($object);
                // 'capture_append'
                foreach($multi_line as $nr => $plugin){
                    $multi_line[$nr] = 'function_' . str_replace('.', '_', $plugin);
                }
                $method = [
                    'if',
                    'elseif',
                    'for',
                    'foreach',
                    'while',
                    'switch',
                    'break',
                    'continue',
                ];
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
            break;
            case Token::TYPE_TAG_CLOSE :
                return Build::TAG_CLOSE;
            break;
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
            break;
            case Token::TYPE_QUOTE_DOUBLE_STRING :
                return Token::TYPE_QUOTE_DOUBLE_STRING;
            break;
            case Token::TYPE_CURLY_CLOSE :
                return Token::TYPE_CURLY_CLOSE;
            break;
            case Token::TYPE_AMPERSAND :
                return Token::TYPE_AMPERSAND;
            break;
            case Token::TYPE_IS_DIVIDE :
                return Token::TYPE_IS_DIVIDE;
            break;
            case Token::TYPE_IS_PLUS_PLUS :
                return Token::TYPE_IS_PLUS_PLUS;
            break;
            case Token::TYPE_IS_MINUS_MINUS :
                return Token::TYPE_IS_MINUS_MINUS;
            break;
            case Token::TYPE_DOC_COMMENT :
                return Token::TYPE_DOC_COMMENT;
            default:
                d($record);
                throw new Exception('Undefined type (' . $record['type'] . ')');
        }
    }

    private function createRequire($document=[]){
        $document = $this->createRequireContent('modifier', $document);
        $document = $this->createRequireContent('function', $document);
        $document = str_replace('function ' . 'capture', 'private function ' . 'capture', $document);
        $document = str_replace('function ' . 'modifier', 'private function ' . 'modifier', $document);
        $document = str_replace('function ' . 'function', 'private function ' . 'function', $document);
        $this->storage()->data('document', $document);
        return $document;
    }

    private function createHeader($document=[]){
        if(empty($document)){
            $document = [];
        }
        $config = $this->object()->data(App::CONFIG);
        $namespace = $this->storage()->data('namespace');
        $document[] = '<?php';
        $document[] = 'namespace ' . $namespace . ';';
        $document[] = '';
        $document[] = '/**';
        $document[] = ' * @copyright                (c) Remco van der Velde 2019 - ' . date('Y');
        $document[] = ' * @version                  ' . $config->data('framework.version');
        $document[] = ' * @license                  MIT';
        $document[] = ' * @note                     Auto generated file, do not modify!';
        $document[] = ' * @author                   R3m\Io\Module\Parse\Build';
        $document[] = ' * @author                   Remco van der Velde';
        if($this->storage()->data('parent')){
            $document[] = ' * @parent                   ' . $this->storage()->data('parent');
        }
        $document[] = ' * @source                   ' . $this->storage()->data('source');
        $document[] = ' * @generation-date          ' . date('Y-m-d H:i:s');
        $document[] = ' * @generation-time          ' . $this->storage()->data('placeholder.generation.time');
        $document[] = ' */';
        $document[] = '';
        $document[] = $this->storage()->data('placeholder.use');
        $this->storage()->data('document', $document);
        return $document;
    }

    public function meta($options=[]){        
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
    public function url($string=null, $options=[]){
        $storage = $this->storage();
        $url = $storage->data('url');
        if($string !== null && $url === null){
            $key = sha1($string);
            $config = $this->object()->data(App::CONFIG);
            $dir = $this->cache_dir();
            $uuid = posix_geteuid();
            if(empty($dir)){
                throw new Exception('Cache dir empty in Build');
            }
            $dir .= $uuid . $config->data('ds');
            $autoload = $this->object()->data(App::NAMESPACE . '.' . Autoload::NAME . '.' . App::R3M);
            $autoload->unregister();
            $autoload->addPrefix($config->data('dictionary.compile'),  $dir);
            $autoload->register();
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
    public function require($type='', $tree=[]){
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

    private function requireModifier($tree=[]){
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

    private function requireFunction($tree=[]){
        $storage = $this->storage();
        foreach($tree as $nr => $record){
            if($record['type'] == Token::TYPE_METHOD){
                $multi_line = Build::getPluginMultiline($this->object());
                // 'capture.append'
                $method = [
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