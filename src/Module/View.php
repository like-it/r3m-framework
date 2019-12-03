<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use stdClass;
use Exception;
use R3m\Io\App;
use R3m\Io\Config;

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

    public function view($object, $url=''){
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);
        $dir = Dir::name($url);
        $file = str_replace($dir, '', $url);
        $dir_template = $dir;
        $dir_base = Dir::name($dir);
        $dir_config = $dir_base . $config->data(Config::DICTIONARY . '.' . Config::DATA) . $config->data('ds');
        $dir_compile = $config->data('parse.dir.compile');
        $dir_cache = $config->data('parse.dir.cache');

        $read = File::read($url);
        $parse = new Parse($object);
        $data = clone $object->data();
        unset($data->{APP::NAMESPACE});
        $data->r3m = new stdClass();
        $data->r3m->config = $config->data();
        $read = $parse->compile($read, $data);
        return $read;
    }

}