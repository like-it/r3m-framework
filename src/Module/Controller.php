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

use R3m\Io\App;
use R3m\Io\Config;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

class Controller {
    const PARSE = 'Parse';
    const TEMPLATE = 'Template';
    const COMPILE = 'Compile';
    const CONFIG = 'Config';
    const CACHE = 'Cache';

    const LDELIM = '{';
    const RDELIM = '}';

    const PROPERTY_LDELIM = 'ldelim';
    const PROPERTY_RDELIM = 'rdelim';
    const PROPERTY_VIEW_URL = 'r3m.io.parse.view.url';
    const PROPERTY_VIEW_MTIME = 'r3m.io.parse.view.mtime';

    const PREPEND = 'prepend';
    const APPEND = 'append';


    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function autoload(App $object, Data $read=null){
        $autoload = $object->data(App::AUTOLOAD_R3M);
        if($read === null){
            $url = $object->config('controller.dir.data') . 'Config' . $object->config('extension.json');
            $read = $object->data_read($url);
        }
        if($read){
            $list = $read->get('autoload');
            if($list && is_array($list)){
                foreach($list as $record){
                    if(
                        property_exists($record, 'prefix') &&
                        property_exists($record, 'directory')
                    ){
                        $addPrefix  = Core::object($record, Core::OBJECT_ARRAY);
                        $addPrefix = Config::parameters($object, $addPrefix);
                        $autoload->addPrefix($addPrefix['prefix'], $addPrefix['directory']);
                        if($object->config('project.log.name')){
                            $object->logger($object->config('project.log.name'))->info('New namespace: ' . $addPrefix['prefix'], [ $addPrefix ]);
                        }
                    }
                }
            }
        }
    }

    public static function name($name='', $before=null, $delimiter='.'): string
    {
        if(
            $before !== null &&
            is_string($before)
        ){
            $before = File::basename($before);
        }
        $name = str_replace(':','.', $name);
        $name = Core::ucfirst_sentence(str_replace('_','.', $name));
        if($before !== null){
            return $before . $delimiter . $name;
        } else {
            return $name;
        }
    }

    public static function plugin(App $object, $dir='', $type=Controller::PREPEND): void
    {
        $plugin = $object->config('parse.dir.plugin');
        if(empty($plugin)){
            $plugin = [];
        }
        if(File::exist($dir)){
            switch($type){
                case Controller::PREPEND:
                    $plugin = [
                        $dir,
                        ...$plugin
                    ];
                break;
                case Controller::APPEND:
                    $plugin[] = $dir;
                break;
            }
        }
        $object->config('parse.dir.plugin', $plugin);
    }

    public static function validator(App $object, $dir='', $type=Controller::PREPEND): void
    {
        $validator = $object->config('validate.dir.validator');
        if(empty($validator)){
            $validator = [];
        }
        if(File::exist($dir)){
            switch($type){
                case Controller::PREPEND:
                    $validator = [
                        $dir,
                        ...$validator
                    ];
                    break;
                case Controller::APPEND:
                    $validator[] = $dir;
                    break;
            }
        }
        $object->config('validate.dir.validator', $validator);
    }

    /**
     * @throws LocateException
     * @throws FileWriteException
     * @throws ObjectException
     * @throws Exception
     */
    public static function locate(App $object, $template=null){
        $temp = $object->data('template');
        $called = '';
        $url = false;
        $list = [];
        if(
            !empty($template) &&
            is_object($template) &&
            property_exists($template, 'url')
        ){
            $url = $template->url;
        }
        if(
            $template === null &&
            !empty($temp) &&
            is_object($temp) &&
            property_exists($temp, 'dir')
        ){
            $dir = $temp->dir;
            $url = false;
        }
        if(
            $template === null &&
            !empty($temp) &&
            is_object($temp) &&
            property_exists($temp, 'name')
        ){
            $name = $temp->name;
            $url = false;
        }
        if(
            !empty($template) &&
            is_object($template) &&
            property_exists($template, 'name')
        ){
            $name = $template->name;
        }
        if(
            !empty($template) &&
            is_object($template) &&
            property_exists($template, 'dir')
        ){
            $dir = $template->dir;
        }
        elseif(
            !empty($template) &&
            is_string($template)
        ){
            $called = get_called_class();
            if(defined($called .'::DIR')){
                $dir = $called::DIR;
            } else {
                throw new Exception('Please define const DIR = __DIR__ . DIRECTORY_SEPARATOR; in the controller (' . $called . ').');
            }
            $name = $template;
        }
        elseif(empty($url)) {
            $called = get_called_class();
            if(defined($called .'::DIR')){
                $dir = $called::DIR;
            } else {
                throw new Exception('Please define const DIR = __DIR__ . DIRECTORY_SEPARATOR; in the controller (' . $called . ').');
            }
        }
        $config = $object->data(App::CONFIG);
        if($url){
            $list[] = $url;
        } else {
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
            $temp = explode('\\', $called);
            if(empty($name)){
                $name = array_pop($temp);
            } else {
                $template_explode = explode('.', $name);
                $count = count($template_explode);
                if($count > 2){
                    $dotted_last = array_pop($template_explode);
                    $dotted_first = array_pop($template_explode);
                    $name = implode($config->data('ds'), $template_explode) . $config->data('ds') . $dotted_first . '.' . $dotted_last;
                }
                elseif($count === 2){
                    $dotted_last = array_pop($template_explode);
                    $dotted_first = array_pop($template_explode);
                    $name = $dotted_first . '.' . $dotted_last;
                }
                elseif($count === 1){
                    $dotted = array_pop($template_explode);
                    $name = $dotted;
                }
            }
            $basename = File::basename($name);
            $name = str_replace(
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
                $name
            );
            if(!empty($object->config('controller.dir.view'))){
                $list[] = $object->config('controller.dir.view') .
                    str_replace('.', $object->config('ds'), $name) .
                    $object->config('ds') .
                    $basename . $config->data('extension.tpl')
                ;
                $list[] = $object->config('controller.dir.view') .
                    str_replace('.', $object->config('ds'), $name) .
                    $config->data('extension.tpl')
                ;
                $list[] = $object->config('controller.dir.view') .
                    $name .
                    $config->data('extension.tpl')
                ;
            }
            elseif(!empty($object->config('host.dir.view'))){
                $list[] = $object->config('host.dir.view') .
                    str_replace('.', $object->config('ds'), $name) .
                    $object->config('ds') .
                    $basename .
                    $config->data('extension.tpl')
                ;
                $list[] = $object->config('host.dir.view') .
                    str_replace('.', $object->config('ds'), $name) .
                    $config->data('extension.tpl')
                ;
                $list[] = $object->config('host.dir.view') .
                    $name .
                    $config->data('extension.tpl')
                ;
            }
            for($i = $max; $i > $minimum; $i--){
                $url = implode($config->data('ds'), $explode) . $config->data('ds');
                if($i <= ($max - 2)){
                    $location = str_replace(
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
                        $url . $name
                    );
                    $list[] = str_replace('.', $object->config('ds'), $location) . $config->data('extension.tpl');
                    $list[] = $location . $config->data('extension.tpl');
                }
                array_pop($explode);
                array_pop($explode);
                $explode[] = $config->data('dictionary.view');
            }
        }
        $url = false;
        $view_url = false;
        $read = false;
        if(
            $object->config('ramdisk.url') &&
            empty($object->config('ramdisk.is.disabled'))
        ){
            $first = reset($list);
            $view_url = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                $object->config('dictionary.view') .
                $object->config('ds') .
                Autoload::name_reducer(
                    $object,
                    str_replace('/', '_', $first),
                    $object->config('cache.controller.url.name_length'),
                    $object->config('cache.controller.url.name_separator'),
                    $object->config('cache.controller.url.name_pop_or_shift')
                )
            ;
            $config_dir = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                $object->config('dictionary.view') .
                $object->config('ds')
            ;
            $config_url = $config_dir .
                $object->config('dictionary.view') .
                $object->config('extension.json')
            ;
            $read = $object->data_read($config_url, sha1($config_url));
            if(!$read){
                $read = new Data();
            }
            if(
                File::exist($view_url) &&
                $read->has(sha1($view_url) . '.url') &&
                File::mtime($view_url) === File::mtime($read->get(sha1($view_url) . '.url'))
            ){
                return $view_url;
            }
        }
        foreach($list as $file){
            if(File::exist($file)){
                if(
                    $object->config('ramdisk.url') &&
                    !empty($object->config('ramdisk.is.disabled')) &&
                    $view_url &&
                    $read
                ){
                    //copy to ramdisk
                    $view_dir = Dir::name($view_url);
                    Dir::create($view_dir);
                    File::copy($file, $view_url);
                    File::touch($view_url, filemtime($file));
                    $read->set(sha1($view_url) . '.url', $file);
                    $read->write($config_url);

                    exec('chmod 640 ' . $view_url);
                    exec('chmod 640 ' . $config_url);
                }
                $url = $file;
                break;
            }
        }
        if(empty($url)){
            if($config->data(Config::DATA_FRAMEWORK_ENVIRONMENT) === Config::MODE_DEVELOPMENT){
                throw new LocateException('Cannot find view file', $list);
            } else {
                throw new LocateException('Cannot find view file');
            }
        }
        return $url;
    }

    public static function configure(App $object, $caller=null){
        $config = $object->data(App::CONFIG);
        $key = Config::DATA_PARSE_DIR_TEMPLATE;
        $value = $config->data(Config::DATA_HOST_DIR_CACHE) . Controller::PARSE . $config->data('ds') . Controller::TEMPLATE . $config->data('ds');
        $config->data($key, $value);
        $key = Config::DATA_PARSE_DIR_COMPILE;
        $value = $config->data(Config::DATA_HOST_DIR_CACHE) . Controller::PARSE . $config->data('ds') . Controller::COMPILE . $config->data('ds');
        $value = $config->data(Config::DATA_HOST_DIR_DATA) . Controller::PARSE . $config->data('ds') . Controller::COMPILE . $config->data('ds');
        $config->data($key, $value);
        $key = Config::DATA_PARSE_DIR_CACHE;
        $value = $config->data(Config::DATA_HOST_DIR_CACHE) . Controller::PARSE . $config->data('ds') . Controller::CACHE . $config->data('ds');
        $value = $config->data(Config::DATA_HOST_DIR_DATA) . Controller::PARSE . $config->data('ds') . Controller::COMPILE . $config->data('ds');
        $config->data($key, $value);
        $key = Config::DATA_PARSE_DIR_PLUGIN;
        $value = [];
        if($caller !== null){
            $dir = rtrim($caller::DIR, $config->data('ds')) . $config->data('ds');
        } else {
            $dir = rtrim(get_called_class()::DIR, $config->data(Config::DS)) . $config->data(Config::DS);
        }
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
        $config->data(Config::DATA_CONTROLLER_DIR_NODE,
            $config->data(Config::DATA_CONTROLLER_DIR_ROOT) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::NODE
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
        $config->data(Config::DATA_CONTROLLER_DIR_FUNCTION,
            $config->data(Config::DATA_CONTROLLER_DIR_ROOT) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::FUNCTION
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
            $config->data(Config::DATA_PROJECT_DIR_ROOT) .
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
        $config->data(Config::DATA_CONTROLLER_DIR_ENTITY,
            $config->data(Config::DATA_PROJECT_DIR_SOURCE) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::ENTITY
            ) .
            $config->data(Config::DS)
        );
        $config->data(Config::DATA_CONTROLLER_DIR_REPOSITORY,
            $config->data(Config::DATA_PROJECT_DIR_SOURCE) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::REPOSITORY
            ) .
            $config->data(Config::DS)
        );
        $config->data(Config::DATA_CONTROLLER_DIR_EXECUTE,
            $config->data(Config::DATA_CONTROLLER_DIR_ROOT) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::EXECUTE
            ) .
            $config->data(Config::DS)
        );
        $config->data(Config::DATA_CONTROLLER_DIR_SERVICE,
            $config->data(Config::DATA_CONTROLLER_DIR_ROOT) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::SERVICE
            ) .
            $config->data(Config::DS)
        );
        $config->data(Config::DATA_CONTROLLER_DIR_VALIDATOR,
            $config->data(Config::DATA_CONTROLLER_DIR_ROOT) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::VALIDATOR
            ) .
            $config->data(Config::DS)
        );
        $config->data(Config::DATA_CONTROLLER_DIR_COMPONENT,
            $config->data(Config::DATA_CONTROLLER_DIR_ROOT) .
            $config->data(
                Config::DICTIONARY .
                '.' .
                Config::COMPONENT
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

        if($caller !== null){
            $config->data(Config::DATA_CONTROLLER_CLASS, $caller);
        } else {
            $config->data(Config::DATA_CONTROLLER_CLASS, get_called_class());
        }
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
    }

    /**
     * @throws ObjectException
     * @throws UrlEmptyException
     * @throws UrlNotExistException
     * @throws FileWriteException
     */
    public static function response(App $object, $url, $data=null)
    {
        if(empty($url)){
            throw new UrlEmptyException('Url is empty');
        }
        if(File::exist($url) === false){
            throw new UrlNotExistException('Url (' . $url .') doesn\'t exist');
        }
        $read = File::read($url);
        $mtime = File::mtime($url);
        $parse = new Parse($object);
        $parse->storage()->data(Controller::PROPERTY_VIEW_URL, $url);
        $parse->storage()->data(Controller::PROPERTY_VIEW_MTIME, $mtime);
        if(empty($data)){
            $object->data(Controller::PROPERTY_LDELIM, Controller::LDELIM);
            $object->data(Controller::PROPERTY_RDELIM, Controller::RDELIM);
            $data = clone $object->data();
            unset($data->{App::NAMESPACE});
        }
        elseif(is_array($data)){
            if(!array_key_exists(Controller::PROPERTY_LDELIM, $data)){
                $data[Controller::PROPERTY_LDELIM] = Controller::LDELIM;
            }
            if(!array_key_exists(Controller::PROPERTY_RDELIM, $data)){
                $data[Controller::PROPERTY_RDELIM] = Controller::RDELIM;
            }
        }
        elseif(is_object($data)){
            if(get_class($data) === Data::CLASS){
                $data = $data->data();
            }
            if(!property_exists($data, Controller::PROPERTY_LDELIM)){
                $data->ldelim = Controller::LDELIM;
            }
            if(!property_exists($data, Controller::PROPERTY_RDELIM)){
                $data->rdelim = Controller::RDELIM;
            }
        }
        $original = $read;
        $read = $parse->compile($read, $data, $parse->storage());
        Parse::readback($object, $parse, App::SCRIPT);
        Parse::readback($object, $parse, App::LINK);
        return $read;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     */
    public static function parse_read(App $object, $url): void
    {
        $read = $object->parse_read($url, sha1($url));
        if($read){
            $object->data($read->data());
        }
    }

    /**
     * @throws UrlEmptyException
     */
    public static function redirect($url=''){
        Core::redirect($url);
    }

    public static function route(App $object, $name, $options=[]){
        return $object->route()::find($object, $name, $options);
    }
}