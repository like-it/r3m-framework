<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module\Parse;

use stdClass;
use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Autoload;
use R3m\Io\Module\Parse;

class Build {
    const NAME = 'Build';

    const VARIABLE_ASSIGN = 'variable-assign';
    const VARIABLE_DEFINE = 'variable-define';
    const METHOD = 'method';
    const METHOD_CONTROL = 'method-control';
    const CODE = 'code';
    const ELSE = 'else';
    const TAG_CLOSE = 'tag-close';

    public $indent;
    private $object;
    private $storage;
    private $cache_dir;
    private $is_debug;

    public function __construct($object=null, $is_debug=false){
        $this->is_debug = $is_debug;
        $this->object($object);

        $config = $this->object()->data(App::CONFIG);

        if(empty($config)){
            throw new Exception('Config not found in object');
        }
        /*
        $compile = $config->data('dictionary.compile');
        if(empty($compile)){
            $config->data('dictionary.compile', Build::COMPILE);
        }
        $template = $config->data('dictionary.template');
        if(empty($template)){
            $config->data('dictionary.template', Build::TEMPLATE);
        }
        */
        $this->storage(new Data());

        $this->storage()->data('time.start', microtime(true));
        $this->storage()->data('placeholder.generation.time', '// R3M-IO-' . Core::uuid());
        $this->storage()->data('placeholder.run', '// R3M-IO-' . Core::uuid());
        $this->storage()->data('placeholder.function', '// R3M-IO-' . Core::uuid());

        $this->storage()->data('use.Exception', new stdClass());
        $this->storage()->data('use.R3m\\Io\\App', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Core', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Parse', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Data', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Route', new stdClass());
        $this->storage()->data('use.R3m\\Io\\Module\\Template\\Main', new stdClass());
        $debug_url = $this->object()->data('controller.dir.data') . 'Debug.info';
        $this->storage()->data('debug.url', $debug_url);
        $dir_plugin = $config->data('parse.dir.plugin');

        if(empty($dir_plugin)){
            $dir_plugin = [];
            $dir_plugin[] = $config->data('host.dir.plugin');
            $dir_plugin[] = $config->data('controller.dir.plugin');
            $dir_plugin[] = $config->data('project.dir.plugin');
            $dir_plugin[] = $config->data('framework.dir.plugin');
        }
        else {
//             $dir_plugin[] = $config->data('controller.dir.plugin');
        }
//         d($dir_plugin);
        $this->storage()->data('plugin', $dir_plugin);
    }

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
        $class = $config->data('dictionary.template') . '_' . $key;
//         $storage->data('class', $class);

//         $document[] = '';
        $document[] = $this->indent(0) . 'class ' . $class . ' extends Main {';
        /*
        $document[] = $this->indent(1) . 'private $parse;';
        $document[] = $this->indent(1) . 'private $storage;';
        $document[] = '';
        $document[] = $this->indent(1) . 'public function __construct(Parse $parse, Data $storage){';
        $document[] = $this->indent(2) . '$this->parse($parse);';
        $document[] = $this->indent(2) . '$this->storage($storage);';
        $document[] = $this->indent(1) . '}';
        $document[] = '';
        */
        $document[] = '';
        $document[] = $this->indent(1) . 'public function run(){';
        $document[] = $this->indent(2) . 'ob_start();';
        $document[] = $this->indent(0) . $storage->data('placeholder.run');
        $document[] = $this->indent(2) . 'return ob_get_clean();';
        $document[] = $this->indent(1) . '}';
        $document[] = '';
        $document[] = $this->indent(0) . $storage->data('placeholder.function');
        $this->indent(2);

        /*
        $document[] = $this->indent(1) . 'public function parse($parse=null){';
        $document[] = $this->indent(2) . 'if($parse !== null){';
        $document[] = $this->indent(3) . '$this->setParse($parse);';
        $document[] = $this->indent(2) . '}';
        $document[] = $this->indent(2) . 'return $this->getParse();';
        $document[] = $this->indent(1) . '}';
        $document[] = '';
        $document[] = $this->indent(1) . 'private function setParse($parse=null){';
        $document[] = $this->indent(2) . '$this->parse = $parse;';
        $document[] = $this->indent(1) . '}';
        $document[] = '';
        $document[] = $this->indent(1) . 'private function getParse(){';
        $document[] = $this->indent(2) . 'return $this->parse;';
        $document[] = $this->indent(1) . '}';
        $document[] = '';
        $document[] = $this->indent(1) . 'public function storage($storage=null){';
        $document[] = $this->indent(2) . 'if($storage !== null){';
        $document[] = $this->indent(3) . '$this->setStorage($storage);';
        $document[] = $this->indent(2) . '}';
        $document[] = $this->indent(2) . 'return $this->getStorage();';
        $document[] = $this->indent(1) . '}';
        $document[] = '';
        $document[] = $this->indent(1) . 'private function setStorage($storage=null){';
        $document[] = $this->indent(2) . '$this->storage = $storage;';
        $document[] = $this->indent(1) . '}';
        $document[] = '';
        $document[] = $this->indent(1) . 'private function getStorage(){';
        $document[] = $this->indent(2) . 'return $this->storage;';
        $document[] = $this->indent(1) . '}';
        $document[] = $this->indent(0) . '}';
        $document[] = '';
        */
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
        } else {
            dd($document);
        }

        return $document;
    }

    private function createRequireContent($type='', $document=[]){
        $config = $this->object()->data(App::CONFIG);
        $storage = $this->storage();

        $dir_plugin = $storage->data('plugin');
        $data = $storage->data($type);

//         dd($storage->data());

        if(empty($data)){
            return $document;
        }

        $placeholder = $storage->data('placeholder.function');
        foreach($data as $name => $record){
            $exist = false;
            foreach($dir_plugin as $nr => $dir){
                $file = ucfirst($name) . $config->data('extension.php');
                $url = $dir . $file;
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
//                     $document[] = 'if(!function_exists(\'R3m\\Io\\Module\\Compile\\' . $name . '\')){';
//                     $document[] = $read;
//                     $document[] = '}';
                }
            }
            if($exist === false){
                $value = $record['value'];
                $text = $name . ' near ' . $record['value'] . ' on line: ' . $record['row'] . ' column: ' . $record['column'] . ' in: ' . $storage->data('source');
                throw new Exception('Function not found: ' . $text);
            }
        }
//
//         $document = str_replace('function function_', 'private function function_', $document);
//         $storage->data('use.stdClass', new stdClass());
//         $storage->data('use.Exception', new stdClass());
//         $storage->data('use.R3m\\Io\\Module\\File', new stdClass());
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
        $storage->data('use.stdClass', new stdClass());
        $storage->data('use.Exception', new stdClass());
        $storage->data('use.R3m\\Io\\Module\\File', new stdClass());
        return $document;
    }

    public function write($url, $document=[]){
        $write = implode("\n", $document);
        $this->storage()->data('time.end', microtime(true));
        $this->storage()->data('time.duration', $this->storage()->data('time.end') - $this->storage()->data('time.start'));
        $write = str_replace($this->storage()->data('placeholder.generation.time'), round($this->storage()->data('time.duration') * 1000, 2). ' msec', $write);
        /*
        bugfix, sometimes }} happens
        */
        $write = str_replace(
            [
                '}
}',
                '}

}',
'}

}',
'}
}'
            ],
            '}',
            $write
        );
        $dir = Dir::name($url);
        $create = Dir::create($dir);
        return File::write($url, $write);
    }

    public static function getPluginMultiline($object=''){
        $config = $object->data(App::CONFIG);
        $array = $config->data('parse.plugin.multi_line');
        return $array;
    }

    public function document($tree=[], $document=[], Data $data){
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
        if($is_debug == 'assign'){
//             dd($tree);
        }
//         dd($tree);
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
            if($is_debug == 'assign'){
//                 d($record);
            }
            if(
                $is_tag === false &&
                $record['type'] == Token::TYPE_STRING
            ){
                $run[] = $this->indent() . 'echo \'' . str_replace('\'', '\\\'', $record['value']) . '\';';
            }
            elseif(
                $is_tag === false &&
                $record['type'] == Token::TYPE_QUOTE_DOUBLE_STRING
            ){
                if($this->is_debug == 'select'){
                    d($tree);
                    dd($record);
                }

//                 d($record['value']);

                /*
                if(
                    in_array(
                        substr($record['value'], 0, 1),
                        [
                            '\'',
                            '"'
                    ]))
                */

//                 $counter++;
                $run[] =  $this->indent() . '$string = \'' . str_replace('\'', '\\\'', substr($record['value'], 1, -1)). '\';';
                $run[] =  $this->indent() . '$string = $this->parse()->compile($string, [], $this->storage());';
                $run[] =  $this->indent() .  'echo \'"\' . $string . \'"\';';
            }
            elseif($record['type'] == Token::TYPE_CURLY_OPEN){
                $is_tag = true;
//                 $selection[$nr] = $record;
                continue;
            }
            elseif($record['type'] == Token::TYPE_CURLY_CLOSE){
//                 $selection[$nr] = $record;

                switch($type){
                    case Token::TYPE_STRING :
                        dd($selection);
                    break;
                    case Token::TYPE_CURLY_CLOSE :
                        dd($selection);
                    break;
                    case Build::VARIABLE_ASSIGN :
                        if($is_debug == 'assign'){
//                             d($selection);
//                             dd($select);
                        }
//                         d($is_debug);
//                         dd($selection);
                        $run[] = $this->indent() . Variable::assign($this, $storage, $selection, false) . ';';
                        if($is_debug == 'assign'){
//                             d($run);
                        }
                    break;
                    case Build::VARIABLE_DEFINE :
                        /*
                        if($is_debug == 'assign'){
                            dd($selection);
                        }
                        */

//                         d($selection);
                        $run[] = $this->indent() . '$variable = ' . Variable::define($this,$storage, $selection) . ';';
                        $run[] = $this->indent() . 'if (is_object($variable)){ return $variable; }';
                        $run[] = $this->indent() . 'elseif (is_array($variable)){ return $variable; }';
                        $run[] = $this->indent() . 'else { echo $variable; } ';
                    break;
                    case Build::METHOD :
//                         d($select);
                        $run[] = $this->indent() . '$method = ' . Method::create($this, $storage, $selection) . ';';
                        $run[] = $this->indent() . 'if (is_object($method)){ return $method; }';
                        $run[] = $this->indent() . 'elseif (is_array($method)){ return $method; }';
                        $run[] = $this->indent() . 'else { echo $method; }';
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
                            if($is_debug == 'assign'){
                                $storage->data('is.debug', 'assign');
                            }

                            if($is_debug == 'menu'){
//                                 d($selection);

                            }


                            $selection = Method::capture_selection($this, $storage, $tree, $selection);

                            if($storage->data('is.debug') == 'menu'){
//                                 dd($selection);
                            }

                            /*
                            if($is_debug == 'assign'){
                                $storage->data('is.debug', 'assign');
                                dd($selection);
                            }
                            */

                            $run[] = $this->indent() . Method::create_capture($this, $storage, $selection) . ';';

                            if($is_debug == 'menu'){
//                                 dd($run);
                            }


                            foreach($selection as $skip_nr => $item){
                                //need skip_nr
                            }

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
//                                 d($control);
                                $run[] = $this->indent() . $control . ' {';
                                $this->indent($this->indent+1);
                                $is_control = true;
                            }
                            $control = null;
                        }
                    break;
                    case Build::ELSE :
                        $this->indent($this->indent-1);
                        $run[] = $this->indent() . '} else {';
                        $this->indent($this->indent+1);
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
                        /*
                        if($select['tag']['name'] != '/capture.append'){
                            $this->indent($this->indent-1);
                            $run[] = $this->indent() . '}';
                        }
                        */
                    break;
                    case Build::CODE :
//                         dd($selection);
                    break;
                    case Token::TYPE_QUOTE_DOUBLE_STRING :
                        d($selection);



//                         $parse = new Parse($this->object());


//                         d($run);
//                         d($select);
//                         dd($selection);
                    default:
                        if($type !== null){
                            d($selection);
                            d($type);
                            dd($record);
                            //                         d($is_tag);
                            //                         die;
                            throw new Exception('type (' . $type . ') undefined');
                        }
                }
                $is_tag = false;
                $selection = [];
                $type = null;
            }
            if($is_tag !== false){
                if($type === null){
//                     d($tree);
                    $type = Build::getType($this->object(), $record);
//                     d($type);
                    $select = $record;
                }
                $selection[$nr] = $record;
            } else {
//                 echo $record['value'];

//                 d($record);
//                 $type = Build::getType($record);
//                 $select = $record;
            }
        }
        /*
        if($is_debug == 'assign'){
            dd($run);
        }
        */
        $storage->data('run', $run);
        return $document;
    }

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
//                             'if',
//                             'elseif',
//                             'for',
//                             'foreach',
//                             'while',
//                             'switch',
//                             'break',
//                             'continue',
//                             'capture_append'
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
            case Token::TYPE_CURLY_CLOSE :
                return Token::TYPE_CURLY_CLOSE;
            case Token::TYPE_AMPERSAND :
                return Token::TYPE_AMPERSAND;


            default:
                $debug = debug_backtrace(true);
//                 d($debug);
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
//         d($this->storage()->data('source'));
        $document[] = ' * @generation-date           ' . date('Y-m-d H:i:s');
        $document[] = ' * @generation-time          ' . $this->storage()->data('placeholder.generation.time');
        $document[] = ' */';
        $document[] = '';
        $document[] = $this->storage()->data('placeholder.use');

        $this->storage()->data('document', $document);

        return $document;
    }

    public function meta(){
        $config = $this->object()->data(App::CONFIG);
        $this->storage()->data('placeholder.use', '// R3M-IO-' . Core::uuid());

        $namespace = 'R3m\\Io\\Module\\' .  $config->data('dictionary.compile');

        $this->storage()->data('namespace', $namespace);

        $key = $this->storage()->data('key');
        $class = $config->data('dictionary.template') . '_' . $key;
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

            $url =
                $dir .
                $config->data('dictionary.template') .
                '_' .
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

//             d($storage);


            $this->meta();
        }
        return $url;
    }

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
//                             'if',
//                             'else.if',
//                             'elseif',
//                             'for',
//                             'for.each',
//                             'foreach',
//                             'while',
//                             'switch',
//                             'break',
//                             'continue',
//                             'capture.append'
                    )
                ){
                    $name = 'function_' . str_replace('.', '_', $record['method']['name']);
                    $storage->data('function.' . $name, $record);
                } else {
                    $multi_line = Build::getPluginMultiline($this->object());
                    if(
                        in_array(
                            $record['method']['name'],
                            $multi_line
//                                 'capture.prepend',
//                                 'capture.append'
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