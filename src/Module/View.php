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
use R3m\Io\Module\File;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class View {
    const PARSE = 'Parse';
    const TEMPLATE = 'Template';
    const COMPILE = 'Compile';
    const CONFIG = 'Config';
    const CACHE = 'Cache';

    public static function name($name='', $before=null, $delimiter='.'){
        if(
            $before !== null &&
            is_string($before)
        ){
            $before = File::basename($before);
        }
        $name = Core::ucfirst_sentence(str_replace('_','.', $name));
        if($before !== null){
            return $before . $delimiter . $name;
        } else {
            return $name;
        }
    }

    public static function view($object, $url){
        return View::response($object, $url);
    }

    /**
     * @throws LocateException
     */
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
            if(defined($called .'::DIR')){
                $dir = $called::DIR;    
            } else {
                $dir = '/';
            }
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
        } else {
            $template_explode = explode('.', $template);
            if(count($template_explode) > 2){
                $dotted_last = array_pop($template_explode);
                $dotted_first = array_pop($template_explode);
                $template = implode($config->data('ds'), $template_explode) . $config->data('ds') . $dotted_first . '.' . $dotted_last;
            }
        }
        for($i = $max; $i > $minimum; $i--){
            $url = implode($config->data('ds'), $explode) . $config->data('ds');
            $list[] = str_replace(
                [
                    '\\', 
                    ':',
                    '='
                ],
                [
                    '/', 
                    '.',
                    '-'
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
                throw new LocateException('Cannot find view file', $list);
            } else {
                throw new LocateException('Cannot find view file');
            }
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
        $config->data(Config::DATA_HOST_DIR_PLUGIN,
            Dir::name($config->data(Config::DATA_CONTROLLER_DIR_ROOT)) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::PLUGIN
            ) .
            $config->data(Config::DS)
        );
        $config->data(Config::DATA_HOST_DIR_PLUGIN_2,
            Dir::name($config->data(Config::DATA_CONTROLLER_DIR_ROOT), 2) .
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
        $value[] = $config->data(Config::DATA_HOST_DIR_PLUGIN);
        $value[] = $config->data(Config::DATA_HOST_DIR_PLUGIN_2);
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

        $config->data(Config::DATA_CONTROLLER_CLASS, get_called_class());
        $config->data(Config::DATA_CONTROLLER_NAME, strtolower(File::basename($config->data(Config::DATA_CONTROLLER_CLASS))));
        $config->data(Config::DATA_CONTROLLER_TITLE, File::basename($config->data(Config::DATA_CONTROLLER_CLASS)));

        $host_dir_public = $config->data(Config::DATA_HOST_DIR_PUBLIC);
        $explode = explode($config->data('ds'), $host_dir_public);
        $slash = array_pop($explode);
        $public = array_pop($explode);
        $extension = array_pop($explode);
        $explode[] = $public;
        $explode[] = $slash;
        $host_dir_public = implode($config->data('ds'), $explode);
        $controller_dir_public = $config->data(Config::DATA_CONTROLLER_DIR_PUBLIC);
        $explode = explode($config->data('ds'), $controller_dir_public);
        $slash = array_pop($explode);
        $public = array_pop($explode);
        $extension = array_pop($explode);
        $explode[] = $public;
        $explode[] = $slash;
        $controller_dir_public = implode($config->data('ds'), $explode);
        if($host_dir_public == $controller_dir_public){
            $controller_dir_public = $config->data(Config::DATA_CONTROLLER_DIR_PUBLIC);
            $controller_dir_public .= $config->data(Config::DATA_CONTROLLER_TITLE) . $config->data('ds');
            $config->data(Config::DATA_CONTROLLER_DIR_PUBLIC, $controller_dir_public);
        }
        $root = $config->data(Config::DATA_CONTROLLER_DIR_ROOT);
        $host = $config->data(Config::DATA_HOST_DIR_ROOT);
        $explode = explode($config->data('ds'), $host);
        array_pop($explode);
        array_pop($explode);
        $host = implode($config->data('ds'), $explode);
        if($host){
            $explode = explode($host, $root, 2);
            if(array_key_exists(1, $explode)){
                $explode = explode($config->data('ds'), $explode[1]);
                if(array_key_exists(1, $explode)){
                    $extension = strtolower($explode[1]);
                    $domain = Host::domain();
                    $subdomain = Host::subdomain();
                    if($subdomain){
                        $config->data(Config::DATA_ROUTE_PREFIX, $subdomain . '-' . $domain . '-' . $extension);
                    } else {
                        $config->data(Config::DATA_ROUTE_PREFIX, $domain . '-' . $extension);
                    }
                }
            }
        }
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
        $object->data('ldelim', '{');
        $object->data('rdelim', '}');
        $data = clone $object->data();
        unset($data->{App::NAMESPACE});
        $read = $parse->compile($read, $data, $parse->storage());

        Parse::readback($object, $parse, App::SCRIPT);
        Parse::readback($object, $parse, App::LINK);
        return $read;
    }
}