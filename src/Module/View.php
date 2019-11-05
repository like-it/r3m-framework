<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Parse;

class View {    
    
    public const PARSE = 'Parse';
    public const TEMPLATE = 'Template';
    public const COMPILE = 'Compile';
    public const CONFIG = 'Config';
    public const CACHE = 'Cache';
    
    
    public static function locate($object, $template=''){
        $called = get_called_class();
        $dir = $called::DIR;
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);        
        if(substr($dir, -1) != $config->data('ds')){
            $dir .= $config->data('ds');
        }        
        $root = $config->data('project.dir.root');
        $explode = explode($config->data('ds'), $root);
        array_pop($explode);        
        $minimum = count($explode);                                
        $explode = explode($config->data('ds'), $dir);
        array_pop($explode);        
        $explode[] = $config->data('dictionary.view');        
        $max = count($explode);        
        $list = [];        
        $temp = explode('\\', $called);
        if(empty($template)){
            $template = array_pop($temp);
        }                               
        for($i = $max; $i > $minimum; $i--){
            $url = implode($config->data('ds'), $explode) . $config->data('ds');
            $list[] = $url . $template . $config->data('extension.tpl');
            array_pop($explode);
            array_pop($explode);
            $explode[] = $config->data('dictionary.view');
        }                               
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);                     
        $url = false;
        foreach($list as $file){
            if(File::exist($file)){
                $url = $file;
                break;
            }
        }
        if(empty($url)){
            if($config->data('framework.environment') == Config::MODE_DEVELOPMENT){                
                d($list);
                throw new Exception('Cannot find view file');
            }
            return;
        }
        return $url;
    }
    
    
    /*
    public static function locate($object, $template=''){
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);               
        $called = get_called_class();       
        if(empty($template)){
            $explode = explode('\\', $called);
            $template = array_pop($explode);
        }
        $list = [];
        $url =
        $config->data('project.dir.root') .
        str_replace(
            '\\',
            $config->data('ds'),
            $called .
            $config->data('ds') .
            $config->data('dictionary.view') .
            $config->data('ds') .
            $template .
            $config->data('extension.tpl')
            );
        $list[] = $url;
        $url =
        $config->data('project.dir.root') .
        str_replace(
            '\\',
            $config->data('ds'),
            implode($config->data('ds'), $explode) .
            $config->data('ds') .
            $config->data('dictionary.view') .
            $config->data('ds') .
            $template .
            $config->data('extension.tpl')
            );
        $list[] = $url;
        $url = $config->data('host.dir.view') . $template . $config->data('extension.tpl');
        $list[] = $url;
        
        $url = false;
        foreach($list as $file){
            if(File::exist($file)){
                $url = $file;
                break;
            }
        }
        if(empty($url)){
            if($config->data('framework.environment') == Config::MODE_DEVELOPMENT){
                d($config->data());
                d($list);
                throw new Exception('Cannot find view file');
            }
            return;
        }
        return $url;
    }
    */
    
    public static function configure($object){
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);
        
        $key = 'parse.dir.template';
        $value = $config->data('host.dir.cache') . View::PARSE . $config->data('ds') . View::TEMPLATE . $config->data('ds');
        $config->data($key, $value);
        
        $key = 'parse.dir.compile';
        $value = $config->data('host.dir.cache') . View::PARSE . $config->data('ds') . View::COMPILE . $config->data('ds');
        $value = $config->data('host.dir.data') . View::PARSE . $config->data('ds') . View::COMPILE . $config->data('ds');
        $config->data($key, $value);
        
        $key = 'parse.dir.cache';
        $value = $config->data('host.dir.cache') . View::PARSE . $config->data('ds') . View::CACHE . $config->data('ds');
        $value = $config->data('host.dir.data') . View::PARSE . $config->data('ds') . View::COMPILE . $config->data('ds');
        $config->data($key, $value);
        
        $key = 'parse.dir.plugin';
        $value = [];
        $value[] =
            $config->data('framework.dir.source') .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::PLUGIN
            ) .
            $config->data(Config::DS)
        ;
        $config->data($key, $value);
        $value[] =
            $config->data('project.dir.source') .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::PLUGIN
            ) .
            $config->data(Config::DS)
        ;
        $config->data($key, $value);
                               
        $key = 'parse.dir.plugin';
        $value = $config->data($key);
        $value[] =
            $config->data('host.dir.root') .
            $config->data(Config::DICTIONARY . '.' . Config::PLUGIN) .
            $config->data('ds')
        ;
        $config->data($key, $value);                                    
    }
    
    /*
    public function view($object, $url=''){
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);
        $dir = Dir::name($url);
        $dir_template = $dir;
                
        $dir_base = Dir::name($dir);
        $dir_config = $dir_base . $config->data(Config::DICTIONARY . '.' . Config::DATA) . $config->data('ds');
        $dir_compile = $config->data('smarty.dir.compile');
        $dir_cache = $config->data('smarty.dir.cache');
        
        $loader = new \Twig\Loader\FilesystemLoader($dir_template);
        $twig = new \Twig\Environment($loader, [
            'cache' => $dir_cache,
        ]);
        
        $template = $twig->load('index.html');
        
        echo $template->render(['the' => 'variables', 'go' => 'here']);
        die;
        
        
        
    }
    */
    
    /**
     * 
     $loader = new \Twig\Loader\FilesystemLoader('/path/to/templates');
$twig = new \Twig\Environment($loader, [
    'cache' => '/path/to/compilation_cache',
]);
     */
    
    public static function view($object, $url){
        
        $read = File::read($url);        
        $data = [
            'title' => 'test'
            
        ];                
        $result = Parse::compile($read, $data);
        
        
        dd($url);
    }
    
    /*
    public static function view($object, $url=''){
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);        
        $dir = Dir::name($url);                
        $dir_template = $dir;
        $dir_base = Dir::name($dir);
        $dir_config = $dir_base . $config->data(Config::DICTIONARY . '.' . Config::DATA) . $config->data('ds');        
        $dir_compile = $config->data('smarty.dir.compile');
        $dir_cache = $config->data('smarty.dir.cache');
        
        $smarty = new Smarty();
        $smarty->setTemplateDir($dir_template);
        $smarty->setCompileDir($dir_compile);
        $smarty->setCacheDir($dir_cache);
        
//         dd($dir_cache);
        
        $smarty->setConfigDir($dir_config);
        
        $object->data('smarty.caller', get_called_class());               
        $list = $config->data('smarty.dir.plugin');        
        if(!empty($list) && is_array($list)){
            foreach ($list as $path){
                if(File::exist($path)){ 
                    d($path);
                    $smarty->setPluginsDir($path);
                }                
            }
        }               
        $data = $object->data('smarty');
        if(
            !empty($data) && 
            (
                is_array($data) || 
                is_object($data)
            )
        ){
            foreach($data as $key => $value){
                $smarty->assign($key, $value);
            }
        }
        $smarty->force_compile = true;
        $smarty->debugging = true;
        $smarty->_debug = true;
        $smarty->error_reporting = E_ALL;        
        $fetch = trim($smarty->fetch($url));              
        return $fetch;
    } 
    */   
}