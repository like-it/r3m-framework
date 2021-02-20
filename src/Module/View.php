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
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class View {
    const PARSE = 'Parse';
    const TEMPLATE = 'Template';
    const COMPILE = 'Compile';
    const CONFIG = 'Config';
    const CACHE = 'Cache';

    public static function view($object, $url){
        return View::response($object, $url);
    }

    public static function locate(App $object, $template=null){
        $temp = $object->data('template');
        $called = '';
        if($template === null && $temp !== null && property_exists($temp, 'dir')){
            $dir = $temp->dir;
        }
        elseif(
            is_object($template) &&
            property_exists($template, 'name') &&
            property_exists($template, 'dir')
        ){
            $dir = $template->dir;
            $template = $template->name;
        }
        else {
            $called = get_called_class();            
            $dir = $called::DIR;
        }
        if($temp !== null && property_exists($temp, 'name')){
            $template = $temp->name;

        }
        $config = $object->data(App::CONFIG);
        if(substr($dir, -1) != $config->data('ds')){
            $dir .= $config->data('ds');
        }
        $root = $config->data(Config::DATA_PROJECT_DIR_ROOT);
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
            $list[] = str_replace(
                [
                    '\\', 
                    ':'
                ],
                [
                    '/', 
                    '.'
                ],
                $url . $template . $config->data('extension.tpl')
            );
            array_pop($explode);
            array_pop($explode);
            $explode[] = $config->data('dictionary.view');
        }        
        $url = false;
        foreach($list as $file){
            if(File::exist($file)){
                $url = $file;
                break;
            }
        }
        if(empty($url)){
            if($config->data(Config::DATA_FRAMEWORK_ENVIRONMENT) == Config::MODE_DEVELOPMENT){
                d($list);                
                throw new LocateException('Cannot find view file');
            }
            return;
        }
        return $url;
    }

    public static function configure(App $object){
        $config = $object->data(App::CONFIG);
        $key = Config::DATA_PARSE_DIR_TEMPLATE;
        $value = $config->data(Config::DATA_HOST_DIR_CACHE) . View::PARSE . $config->data('ds') . View::TEMPLATE . $config->data('ds');
        $config->data($key, $value);
        $key = Config::DATA_PARSE_DIR_COMPILE;
        $value = $config->data(Config::DATA_HOST_DIR_CACHE) . View::PARSE . $config->data('ds') . View::COMPILE . $config->data('ds');
        $value = $config->data(Config::DATA_HOST_DIR_DATA) . View::PARSE . $config->data('ds') . View::COMPILE . $config->data('ds');
        $config->data($key, $value);
        $key = Config::DATA_PARSE_DIR_CACHE;
        $value = $config->data(Config::DATA_HOST_DIR_CACHE) . View::PARSE . $config->data('ds') . View::CACHE . $config->data('ds');
        $value = $config->data(Config::DATA_HOST_DIR_DATA) . View::PARSE . $config->data('ds') . View::COMPILE . $config->data('ds');
        $config->data($key, $value);
        $key = Config::DATA_PARSE_DIR_PLUGIN;
        $value = [];
        $dir = rtrim(get_called_class()::DIR,$config->data(Config::DS)) . $config->data(Config::DS);
        $config->data(Config::DATA_CONTROLLER_DIR_SOURCE, $dir);
        $config->data(Config::DATA_CONTROLLER_DIR_ROOT, Dir::name($dir));
        $config->data(Config::DATA_CONTROLLER_DIR_DATA,
            $config->data(Config::DATA_CONTROLLER_DIR_ROOT) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::DATA
                ) .
            $config->data(Config::DS)
        );
        $config->data(Config::DATA_CONTROLLER_DIR_PLUGIN,
            $config->data(Config::DATA_CONTROLLER_DIR_ROOT) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::PLUGIN
                ) .
            $config->data(Config::DS)
        );
        $config->data(Config::DATA_CONTROLLER_DIR_MODEL,
            $config->data(Config::DATA_CONTROLLER_DIR_ROOT) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::MODEL
                ) .
            $config->data(Config::DS)
        );
        $config->data(Config::DATA_CONTROLLER_DIR_VIEW,
            $config->data(Config::DATA_CONTROLLER_DIR_ROOT) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::VIEW
                ) .
            $config->data(Config::DS)
        );
        $config->data(Config::DATA_CONTROLLER_DIR_PUBLIC,
        	$config->data(Config::DATA_CONTROLLER_DIR_ROOT) .
        	$config->data(
        		Config::DICTIONARY .
        		'.' .
        		Config::PUBLIC
        	) .
        	$config->data(Config::DS)
        );
        $value[] =
        $config->data(Config::DATA_CONTROLLER_DIR_ROOT) .
        $config->data(
            Config::DICTIONARY .
            '.' .
            Config::PLUGIN
            ) .
            $config->data(Config::DS)
        ;
        $host_dir_root = $config->data(Config::DATA_HOST_DIR_ROOT);
        if(!empty($host_dir_root)){
            $value[] =
            $host_dir_root .
            $config->data(Config::DICTIONARY . '.' . Config::PLUGIN) .
            $config->data('ds')
            ;
        }
        $value[] =
            $config->data(Config::DATA_PROJECT_DIR_SOURCE) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::PLUGIN
            ) .
            $config->data(Config::DS)
        ;
        $value[] =
            $config->data(Config::DATA_FRAMEWORK_DIR_SOURCE) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::PLUGIN
                ) .
                $config->data(Config::DS)
        ;
        $config->data($key, $value);  

        $config->data(CONFIG::DATA_CONTROLLER_CLASS, get_called_class());
        $config->data(CONFIG::DATA_CONTROLLER_NAME, strtolower(File::basename($config->data(CONFIG::DATA_CONTROLLER_CLASS))));
        $config->data(CONFIG::DATA_CONTROLLER_TITLE, File::basename($config->data(CONFIG::DATA_CONTROLLER_CLASS)));
        $object->data(CONFIG::DATA_CONTROLLER, $config->data(CONFIG::DATA_CONTROLLER));
    }

    public static function response(App $object, $url){
        if(empty($url)){            
            throw new UrlEmptyException('Url is empty');
        }        
        $config = $object->data(App::CONFIG);
        $dir = Dir::name($url);
        $file = str_replace($dir, '', $url);
        $dir_template = $dir;
        $dir_base = Dir::name($dir);
        $dir_config = $dir_base . $config->data(Config::DICTIONARY . '.' . Config::DATA) . $config->data('ds');
        $dir_compile = $config->data('parse.dir.compile');
        $dir_cache = $config->data('parse.dir.cache');
        if(File::exist($url) === false){
            throw new UrlNotExistException('Url (' . $url .')doesn\'t exist');
        }
        $read = File::read($url);
        $mtime = File::mtime($url);
        $parse = new Parse($object);
        $parse->storage()->data('r3m.io.parse.view.url', $url);
        $parse->storage()->data('r3m.io.parse.view.mtime', $mtime);
        $data = clone $object->data();
        unset($data->{App::NAMESPACE});
        $data->r3m = new stdClass();
        $data->r3m->io = new stdClass();
        $data->r3m->io->config = $config->data();
        $read = $parse->compile($read, $data, $parse->storage());
        Parse::readback($object, $parse, App::SCRIPT);
        Parse::readback($object, $parse, App::LINK);
        return $read;
    }
}