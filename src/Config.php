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
namespace R3m\Io;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

class Config extends Data {
    const NAME = 'Config';

    const MODE_PRODUCTION = 'production';
    const MODE_DEVELOPMENT = 'development';

    const DATA = 'data';
    const VALUE_DATA = 'Data';

    const PUBLIC = 'public';
    const VALUE_PUBLIC = 'Public';

    const HOST = 'host';
    const VALUE_HOST = 'Host';

    const CACHE = 'cache';
    const VALUE_CACHE = '/tmp/r3m/io/';

    const SOURCE = 'Source';
    const VALUE_SOURCE = 'src';

    const BINARY = 'Binary';
    const VALUE_BINARY = 'Bin';

    const CLI = 'cli';
    const VALUE_CLI = 'Cli';

    const MODULE = 'module';
    const VALUE_MODULE = 'Module';

    const FRAMEWORK = 'framework';
    const VALUE_FRAMEWORK = 'r3m/framework';

    const ENVIRONMENT = 'environment';
    const VALUE_ENVIRONMENT = Config::MODE_PRODUCTION;

    const PLUGIN = 'plugin';
    const VALUE_PLUGIN = 'Plugin';

    const DS = 'ds';
    const VALUE_DS = DIRECTORY_SEPARATOR;

    const VIEW = 'view';
    const VALUE_VIEW = 'View';

    const MODEL = 'model';
    const VALUE_MODEL = 'Model';

    const LOCALHOST_EXTENSION = 'localhost.extension';
    const VALUE_LOCALHOST_EXTENSION =  [
        'local'        
    ];

    const ROUTE = 'Route.json';
    const CONFIG = 'Config.json';

    const DICTIONARY = 'dictionary';

    const DATA_PDO = 'pdo';

    const DATA_DIR_VENDOR = 'dir.vendor';
    const DATA_FRAMEWORK_VERSION = 'framework.version';
    const DATA_FRAMEWORK_BUILT = 'framework.built';
    const DATA_FRAMEWORK_DIR = 'framework.dir';
    const DATA_FRAMEWORK_DIR_ROOT = Config::DATA_FRAMEWORK_DIR . '.' .'root';
    const DATA_FRAMEWORK_DIR_VENDOR = Config::DATA_FRAMEWORK_DIR . '.' .'vendor';
    const DATA_FRAMEWORK_DIR_SOURCE = Config::DATA_FRAMEWORK_DIR . '.' .'source';
    const DATA_FRAMEWORK_DIR_DATA = Config::DATA_FRAMEWORK_DIR . '.' .'data';
    const DATA_FRAMEWORK_DIR_CLI = Config::DATA_FRAMEWORK_DIR . '.' .'cli';
    const DATA_FRAMEWORK_DIR_CACHE = Config::DATA_FRAMEWORK_DIR . '.' .'cache';
    const DATA_FRAMEWORK_DIR_MODULE = Config::DATA_FRAMEWORK_DIR . '.' .'module';
    const DATA_FRAMEWORK_DIR_PLUGIN =  Config::DATA_FRAMEWORK_DIR . '.' . 'plugin';

    const DATA_FRAMEWORK_ENVIRONMENT = 'framework.environment';

    const DATA_PROJECT_ROUTE_FILENAME = 'project.route.filename';
    const DATA_PROJECT_ROUTE_URL = 'project.route.url';
    const DATA_PROJECT_DIR = 'project.dir';
    const DATA_PROJECT_DIR_ROOT =  Config::DATA_PROJECT_DIR . '.' . 'root';
    const DATA_PROJECT_DIR_BINARY =  Config::DATA_PROJECT_DIR . '.' . 'binary';
    const DATA_PROJECT_DIR_VENDOR =  Config::DATA_PROJECT_DIR . '.' . 'vendor';
    const DATA_PROJECT_DIR_SOURCE =  Config::DATA_PROJECT_DIR . '.' . 'source';
    const DATA_PROJECT_DIR_DATA =  Config::DATA_PROJECT_DIR . '.' . 'data';
    const DATA_PROJECT_DIR_CLI =  Config::DATA_PROJECT_DIR . '.' . 'cli';
    const DATA_PROJECT_DIR_PUBLIC =  Config::DATA_PROJECT_DIR . '.' . 'public';
    const DATA_PROJECT_DIR_HOST =  Config::DATA_PROJECT_DIR . '.' . 'host';
    const DATA_PROJECT_DIR_PLUGIN =  Config::DATA_PROJECT_DIR . '.' . 'plugin';

    const DATA_CONTROLLER_DIR = 'controller.dir';
    const DATA_CONTROLLER_DIR_ROOT = Config::DATA_CONTROLLER_DIR . '.' .'root';
    const DATA_CONTROLLER_DIR_SOURCE = Config::DATA_CONTROLLER_DIR . '.' .'source';
    const DATA_CONTROLLER_DIR_DATA = Config::DATA_CONTROLLER_DIR . '.' .'data';
    const DATA_CONTROLLER_DIR_PLUGIN = Config::DATA_CONTROLLER_DIR . '.' .'plugin';
    const DATA_CONTROLLER_DIR_MODEL = Config::DATA_CONTROLLER_DIR . '.' .'model';
    const DATA_CONTROLLER_DIR_VIEW = Config::DATA_CONTROLLER_DIR . '.' .'view';

    public function __construct($config=[]){
        if(array_key_exists(Config::DATA_DIR_VENDOR, $config)){
            $this->data(Config::DATA_PROJECT_DIR_VENDOR, $config[Config::DATA_DIR_VENDOR]);
            $this->data(Config::DATA_PROJECT_DIR_ROOT, dirname($this->data(Config::DATA_PROJECT_DIR_VENDOR)) . '/');
            unset($config[Config::DATA_DIR_VENDOR]);
        }
        $this->default();
        $url = $this->data(Config::DATA_FRAMEWORK_DIR_DATA) . Config::CONFIG;
        if(File::exist($url)){
            $read = Core::object(File::read($url));
            $this->data(Core::object_merge($this->data(), $read));
        }        
        foreach($config as $attribute => $value){
            $this->data($attribute, $value);
        }
    }

    public static function configure($object){
        $config = $object->data(App::CONFIG);
        $url = $config->data(Config::DATA_PROJECT_DIR_DATA) . Config::CONFIG;
        if(File::exist($url)){
            $read = Core::object(File::read($url));
            $config->data(Core::object_merge($config->data(), $read));
        }
    }

    public function default(){
        $key = Config::DICTIONARY . '.' . Config::DATA;
        $value = Config::VALUE_DATA;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::SOURCE;
        $value = Config::VALUE_SOURCE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::BINARY;
        $value = Config::VALUE_BINARY;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::CACHE;
        $value = Config::VALUE_CACHE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::PUBLIC;
        $value = Config::VALUE_PUBLIC;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::MODULE;
        $value = Config::VALUE_MODULE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::CLI;
        $value = Config::VALUE_CLI;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::HOST;
        $value = Config::VALUE_HOST;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::VIEW;
        $value = Config::VALUE_VIEW;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::MODEL;
        $value = Config::VALUE_MODEL;
        $this->data($key, $value);


        $key = Config::DICTIONARY . '.' . Config::FRAMEWORK;
        $value = Config::VALUE_FRAMEWORK;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::ENVIRONMENT;
        $value = Config::VALUE_ENVIRONMENT;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::PLUGIN;
        $value = Config::VALUE_PLUGIN;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::DS;
        $value = Config::VALUE_DS;
        $this->data($key, $value);

        $value = Config::VALUE_DS;
        $key = Config::DS;
        $this->data($key, $value);

        $this->data('extension.php', '.php');
        $this->data('extension.json', '.json');
        $this->data('extension.css', '.css');
        $this->data('extension.js', '.js');
        $this->data('extension.jpg', '.jpg');
        $this->data('extension.gif', '.gif');
        $this->data('extension.png', '.png');
        $this->data('extension.zip', '.zip');
        $this->data('extension.rar', '.rar');
        $this->data('extension.tpl', '.tpl');
        $this->data('extension.conf', '.conf');

        $this->data(Config::LOCALHOST_EXTENSION, Config::VALUE_LOCALHOST_EXTENSION);

        $key = Config::DATA_PROJECT_DIR_SOURCE;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::SOURCE) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_BINARY;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::BINARY) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_DATA;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::DATA) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_CLI;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::CLI) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_PUBLIC;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::PUBLIC) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_HOST;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::HOST) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_ROUTE_FILENAME;
        $value = Config::ROUTE;
        $this->data($key, $value);

        //project.route.url can be configured in index / cli

        $key = Config::DATA_FRAMEWORK_DIR_ROOT;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_VENDOR) .
            $this->data(Config::DICTIONARY . '.' . Config::FRAMEWORK) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_SOURCE;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::SOURCE) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_DATA;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::DATA) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_CACHE;
        $value =
            $this->data(Config::DICTIONARY . '.' . Config::CACHE) .
            $this->data(Config::DICTIONARY . '.' . Config::FRAMEWORK) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_MODULE;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::MODULE) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_CLI;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::CLI) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_ENVIRONMENT;
        $value = $this->data(Config::DICTIONARY . '.' . Config::ENVIRONMENT);
        $this->data($key, $value);
    }

    public static function ucfirst_sentence($string='', $delimiter='.'){
        $explode = explode($delimiter, $string);
        foreach($explode as $nr => $part){
            $explode[$nr] = ucfirst(trim($part));
        }
        return implode($delimiter, $explode);
    }
}