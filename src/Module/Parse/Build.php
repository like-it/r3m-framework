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
    public const TEMPLATE = 'Template';
    public const COMPILE = 'Compile';
    public const NAME = 'Build';

    private const VARIABLE_ASSIGN = 'variable-assign';
    private const VARIABLE_DEFINE = 'variable-define';
    private const METHOD = 'method';
    private const METHOD_CONTROL = 'method-control';
    private const CODE = 'code';
    private const ELSE = 'else';
    private const TAG_CLOSE = 'tag-close';

    public $indent;
    private $object;
    private $storage;

    public function __construct($object=null){
        $this->object($object);

        $config = $this->object()->data(App::NAMESPACE . '.' . Config::NAME);

        if(empty($config)){
            throw new Exception('Config not found in object');
        }

        $compile = $config->data('dictionary.compile');
        if(empty($compile)){
            $config->data('dictionary.compile', Build::COMPILE);
        }
        $template = $config->data('dictionary.template');
        if(empty($template)){
            $config->data('dictionary.template', Build::TEMPLATE);
        }
        $this->storage(new Data());

        $dir_plugin = [];
        $dir_plugin[] = $config->data('host.dir.plugin');
        $dir_plugin[] = $config->data('project.dir.plugin');
        $dir_plugin[] = $config->data('framework.dir.plugin');

        $this->storage()->data('plugin', $dir_plugin);
    }

    public function create($type='', $document=[]){
        switch($type){
            case 'header' :
                return $this->createHeader($document);
            break;
            case 'require' :
                return $this->createRequire($document);
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
        $config = $this->object()->data(App::NAMESPACE . '.' . Config::NAME);

        $storage = $this->storage();
        $key = $storage->data('key');
        $class = $config->data('dictionary.template') . '_' . $key;
        $storage->data('class', $class);


//         $storage->data('use.R3m\\Io\\Module\\Parse', new stdClass());
//         $storage->data('use.R3m\\Io\\Module\\Data', new stdClass());
        $storage->data('use.R3m\\Io\\Module\\Template\\Main', new stdClass());

        $storage->data('placeholder.run', '// R3M-IO-' . Core::uuid());

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
        foreach($document as $nr => $row){
            $document[$nr] = str_replace($storage->data('placeholder.run'), $content, $row, $count);
            if($count > 0){
                break;
            }
        }
        return $document;
    }

    private function createRequireCategory($type='', $document=[]){
        $config = $this->object()->data(App::NAMESPACE . '.' . Config::NAME);
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

        $dir = Dir::name($url);
        $create = Dir::create($dir);

//         echo $write;

        return File::write($url, $write);
    }

    public function document($tree=[], $document=[], Data $data){
        $is_tag = false;
        $tag = null;
        $this->indent(2);
        $counter = 0;
        $storage = $this->storage();

        $run = $storage->data('run');
        if(empty($run)){
            $run = [];
        }
        $type = null;
        $select = null;
        $selection = [];

        $skip_nr = null;

        $is_control = false;

//         d($tree);
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
                $run[] = $this->indent() . 'echo \'' . str_replace('\'', '\\\'', $record['value']) . '\';';
            }
            elseif($record['type'] == Token::TYPE_QUOTE_DOUBLE_STRING){
                $counter++;
                $run[] =  $this->indent() . '$string = \'' . str_replace('\'', '\\\'', substr($record['value'], 1, -1)). '\';';
                $run[] =  $this->indent() . '$string = $this->parse()->compile($string, [], $this->storage(), ' . $counter . ');';
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
                    case Build::VARIABLE_ASSIGN :
                        $run[] = $this->indent() . Variable::assign($this, $selection, $storage) . ';';
                    break;
                    case Build::VARIABLE_DEFINE :
//                         d($selection);
                        $run[] = $this->indent() . 'echo' . ' ' . Variable::define($this, $selection, $storage) . ';';
                    break;
                    case Build::METHOD :
//                         d($select);
                        $run[] = $this->indent() . 'echo' . ' ' . Method::create($this, $selection, $storage) . ';';
//                         $run[] = $this->indent() . Method::create($this, $selection, $storage) . ';';
                    break;
                    case Build::METHOD_CONTROL :
                        if($select['method']['name'] == 'capture.append'){
                            $selection = Method::capture_selection($this, $tree, $selection, $storage);
                            $run[] = $this->indent() . Method::create_capture($this, $selection, $storage) . ';';
                            foreach($selection as $skip_nr => $item){

                            }
                        } else {
                            $control = Method::create_control($this, $selection, $storage);
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
                        if($select['tag']['name'] != '/capture.append'){
                            $this->indent($this->indent-1);
                            $run[] = $this->indent() . '}';
                        }
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
                        throw new Exception('type (' . $type . ') undefined');

                }
                $is_tag = false;
                $selection = [];
                $type = null;
            }
            if($is_tag !== false){
                if($type === null){
//                     d($tree);
                    $type = Build::getType($record);
                    $select = $record;
                }
                $selection[$nr] = $record;
            }

        }
        $storage->data('run', $run);
        return $document;
    }

    private static function getType($record=[]){
        switch($record['type']){
            case Token::TYPE_VARIABLE :
                if($record['variable']['is_assign'] === true){
                    return Build::VARIABLE_ASSIGN;
                } else {
                    return Build::VARIABLE_DEFINE;
                }
            break;
            case Token::TYPE_METHOD :
                if(
                    in_array(
                        $record['method']['php_name'],
                        [
                            'if',
                            'elseif',
                            'for',
                            'foreach',
                            'while',
                            'switch',
                            'break',
                            'continue',
                            'capture',
                            'capture_append'
                        ]
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
                $debug = debug_backtrace(true);
                d($debug);
                d($record);
                break;
            case Token::TYPE_QUOTE_DOUBLE_STRING :
                return Token::TYPE_QUOTE_DOUBLE_STRING;
            default:
                $debug = debug_backtrace(true);
                d($debug);
                d($record);
                throw new Exception('Undefined type (' . $record['type'] . ')');

        }
    }

    private function createRequire($document=[]){
        $document = $this->createRequireCategory('modifier', $document);
        $document = $this->createRequireCategory('function', $document);

        $this->storage()->data('document', $document);

        return $document;
    }

    private function createHeader($document=[]){
        if(empty($document)){
            $document = [];
        }
        $config = $this->object()->data(App::NAMESPACE . '.' . Config::NAME);
        $this->storage()->data('placeholder.use', '// R3M-IO-' . Core::uuid());

        $namespace = 'R3m\\Io\\Module\\' .  $config->data('dictionary.compile');

        $this->storage()->data('namespace', $namespace);

        $document[] = '<?php';
        $document[] = 'namespace ' . $namespace . ';';
        $document[] = '';
        $document[] = '/**';
        $document[] = ' * @copyright                (c) https://r3m.io 2019 - ' . date('Y');
        $document[] = ' * @version                  1.0';
        $document[] = ' * @note                     Auto generated file, do not modify!';
        $document[] = ' * @author                   R3m\Io\Module\Parse\Build';
        $document[] = ' * @author                   Remco van der Velde';
        $document[] = ' */';
        $document[] = '';
        $document[] = $this->storage()->data('placeholder.use');

        $this->storage()->data('document', $document);

        return $document;
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

    public function url($string=null){
        $storage = $this->storage();
        $url = $storage->data('url');
        if($string !== null && $url === null){
            $key = sha1($string);

            $config = $this->object()->data(App::NAMESPACE . '.' . Config::NAME);
            $dir =
                $config->data('project.dir.data') .
                $config->data('dictionary.compile') .
                $config->data('ds');

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
            if($record['type'] == Token::TYPE_VARIABLE && array_key_exists('has_modifier', $record['variable'])){
                foreach($record['variable']['modifier'] as $modifier_list_nr => $modifier_list){
                    foreach($modifier_list as $modifier_nr => $modifier){
                        if(
                            array_key_exists('type', $modifier) &&
                            $modifier['type'] == Token::TYPE_MODIFIER
                        ){
                            $name = 'modifier_' . str_replace('.', '_', $modifier['value']);
                            $tree[$nr]['variable']['modifier'][$modifier_list_nr][$modifier_nr]['php_name'] = $name;
                            $storage->data('modifier.' . $name, new stdClass());
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
                if(
                    !in_array(
                        $record['method']['name'],
                        [
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
                            'capture',
                            'capture.append'
                        ]
                    )
                ){
                    $name = 'function_' . str_replace('.', '_', $record['method']['name']);
                    $storage->data('function.' . $name, new stdClass());
                } else {
                    if(
                        in_array(
                            $record['method']['name'],
                            [
                                'capture.append'
                            ]
                        )
                    ){
                        $name = str_replace('.', '_', $record['method']['name']);
                        $storage->data('function.' . $name, new stdClass());
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